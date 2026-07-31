/**
 * 3D Video Scroll — high-performance scroll-scrub engine.
 *
 * Lag later in the scroll is usually from:
 *  - stacked / interrupted video seeks
 *  - seeking to every tiny scroll delta instead of whole frames
 *  - expensive layout reads + DOM writes on every animation frame
 *
 * This build: single target seek queue, frame-snap, cached metrics,
 * UI updates only when frame/scene changes, scrub from smoothScroll while wheeling.
 */
(function ($) {
    'use strict';

    function clamp(value, min, max) {
        return Math.max(min, Math.min(max, value));
    }

    function parseConfig(scrollHero) {
        var defaults = {
            sceneTimes: [0, 5.8, 11.7, 17.6, 23.4],
            duration: 30,
            fps: 15
        };

        var raw = scrollHero.getAttribute('data-emha-config');
        if (!raw) {
            return defaults;
        }

        try {
            var parsed = JSON.parse(raw);
            return {
                sceneTimes: Array.isArray(parsed.sceneTimes) && parsed.sceneTimes.length
                    ? parsed.sceneTimes.map(Number)
                    : defaults.sceneTimes,
                duration: Number(parsed.duration) > 0 ? Number(parsed.duration) : defaults.duration,
                fps: Number(parsed.fps) > 0 ? Number(parsed.fps) : defaults.fps
            };
        } catch (e) {
            return defaults;
        }
    }

    function initVideoScrollInstance(root) {
        if (!root || root.getAttribute('data-emha-vs-ready') === '1') {
            return;
        }

        var scrollHero = root.classList && root.classList.contains('rs-scroll-hero')
            ? root
            : root.querySelector('.rs-scroll-hero');

        if (!scrollHero) {
            return;
        }

        root.setAttribute('data-emha-vs-ready', '1');
        scrollHero.setAttribute('data-emha-vs-ready', '1');

        var config = parseConfig(scrollHero);
        var sceneTimes = config.sceneTimes;
        var duration = config.duration;
        var sourceFps = config.fps;
        var frameStep = 1 / sourceFps;

        var scrollStage = scrollHero.querySelector('.rs-scroll-stage');
        var video = scrollHero.querySelector('.rs-scroll-video');
        var scenes = Array.prototype.slice.call(scrollHero.querySelectorAll('.rs-scroll-scene'));

        if (!scrollStage || !video) {
            return;
        }

        var totalFrames = Math.max(1, Math.round(duration * sourceFps));
        var playbackEnd = Math.max(frameStep, duration - 0.08);

        // --- scroll / wheel state ---
        var virtualScroll = window.pageYOffset || window.scrollY || 0;
        var smoothScroll = virtualScroll;
        var wheelFrame = 0;
        var wheelActive = false;
        var navDragging = false;

        // --- scrub / seek state (performance critical) ---
        var rafPending = false;
        var targetTime = 0;
        var targetFrame = -1;
        var appliedFrame = -1;
        var seekInFlight = false;
        var activeScene = -1;
        var lastNavFrame = -1;
        var lastProgressBucket = -1;
        var pausedEnsured = false;

        // --- cached layout metrics (avoid offsetParent walks every frame) ---
        var metrics = { start: 0, range: 1, end: 1 };
        var metricsValid = false;
        var resizeTimer = 0;

        // Frame navigator
        var videoNav = document.createElement('div');
        videoNav.className = 'rs-video-nav';
        videoNav.setAttribute('aria-label', 'Property video navigator');
        videoNav.innerHTML =
            '<span class="rs-video-nav-label">FRAMES</span>' +
            '<div class="rs-video-track" role="slider" tabindex="0" ' +
            'aria-label="Property video frame" aria-valuemin="1" ' +
            'aria-valuemax="' + totalFrames + '" aria-valuenow="1">' +
            '<span class="rs-video-fill"></span>' +
            '<span class="rs-video-thumb"></span></div>' +
            '<span class="rs-video-progress">001 / ' +
            String(totalFrames).padStart(3, '0') + '</span>';

        scrollStage.appendChild(videoNav);
        var navTrack = videoNav.querySelector('.rs-video-track');
        var navProgress = videoNav.querySelector('.rs-video-progress');

        function recomputeMetrics() {
            // getBoundingClientRect + page offset is accurate inside Elementor wrappers.
            var rect = scrollHero.getBoundingClientRect();
            var pageY = window.pageYOffset || window.scrollY || 0;
            var start = rect.top + pageY;
            var height = scrollHero.offsetHeight || rect.height;
            var range = Math.max(1, height - window.innerHeight);

            metrics.start = start;
            metrics.range = range;
            metrics.end = start + range;
            metricsValid = true;
            return metrics;
        }

        function getMetrics() {
            if (!metricsValid) {
                return recomputeMetrics();
            }
            return metrics;
        }

        function sceneIndexAt(time) {
            var active = 0;
            for (var i = sceneTimes.length - 1; i >= 0; i--) {
                if (time >= sceneTimes[i]) {
                    active = i;
                    break;
                }
            }
            return active;
        }

        function setSceneIfChanged(time) {
            var next = sceneIndexAt(time);
            if (next === activeScene) {
                return;
            }
            activeScene = next;
            for (var i = 0; i < scenes.length; i++) {
                // classList.toggle with force avoids unnecessary style recalc when unchanged
                scenes[i].classList.toggle('rs-scene-active', i === next);
            }
        }

        function updateNavigatorIfChanged(progress) {
            var safeProgress = clamp(progress, 0, 1);
            // Bucket progress to ~0.1% so we don't thrash CSS vars every pixel.
            var bucket = (safeProgress * 1000) | 0;
            var frame = Math.min(
                totalFrames,
                Math.max(1, Math.round(safeProgress * (totalFrames - 1)) + 1)
            );

            if (bucket === lastProgressBucket && frame === lastNavFrame) {
                return;
            }
            lastProgressBucket = bucket;
            lastNavFrame = frame;

            var percent = (safeProgress * 100).toFixed(2) + '%';

            scrollStage.style.setProperty('--scroll-progress', safeProgress.toFixed(4));
            videoNav.style.setProperty('--video-progress', percent);

            if (navTrack) {
                navTrack.setAttribute('aria-valuenow', String(frame));
            }
            if (navProgress) {
                navProgress.textContent =
                    String(frame).padStart(3, '0') +
                    ' / ' +
                    String(totalFrames).padStart(3, '0');
            }
        }

        /**
         * Issue at most one seek at a time. Always jump to the *latest* target frame
         * when the previous seek finishes (drop intermediate frames = no lag pile-up).
         */
        function issueSeek() {
            if (seekInFlight || video.readyState < 1) {
                return;
            }

            if (targetFrame === appliedFrame) {
                return;
            }

            var nextTime = Math.min(playbackEnd, Math.max(0, targetFrame * frameStep));

            // Already on (or extremely close to) the target frame.
            if (Math.abs((video.currentTime || 0) - nextTime) < frameStep * 0.35) {
                appliedFrame = targetFrame;
                return;
            }

            seekInFlight = true;
            appliedFrame = targetFrame;

            try {
                // Prefer precise frame scrub; fastSeek is approximate and can look jumpy.
                video.currentTime = nextTime;
            } catch (error) {
                seekInFlight = false;
                appliedFrame = -1;
            }
        }

        function onSeeked() {
            seekInFlight = false;

            // If user scrolled further while we were seeking, catch up to latest frame only.
            if (targetFrame !== appliedFrame) {
                issueSeek();
            }
        }

        function ensurePaused() {
            if (!pausedEnsured || !video.paused) {
                try {
                    if (!video.paused) {
                        video.pause();
                    }
                } catch (e) { /* ignore */ }
                pausedEnsured = true;
            }
        }

        function scrubVideo() {
            rafPending = false;

            if (video.readyState < 1) {
                return;
            }

            ensurePaused();

            var m = getMetrics();
            // While custom wheel smoothing runs, use the animated scroll position
            // (window.scrollY can lag a frame behind scrollTo).
            var scrollY = wheelActive
                ? smoothScroll
                : (window.pageYOffset || window.scrollY || 0);

            var progress = clamp((scrollY - m.start) / m.range, 0, 1);
            var exactTime = progress * playbackEnd;

            // Snap to whole source frames — never seek to sub-frame times.
            targetFrame = Math.min(
                totalFrames - 1,
                Math.max(0, Math.round(exactTime * sourceFps))
            );
            targetTime = targetFrame * frameStep;

            // UI can stay smooth even if the video decoder is catching up.
            updateNavigatorIfChanged(progress);
            setSceneIfChanged(exactTime);

            issueSeek();
        }

        function requestScrub() {
            if (!rafPending) {
                rafPending = true;
                requestAnimationFrame(scrubVideo);
            }
        }

        function finishSmoothScroll() {
            smoothScroll = virtualScroll;
            window.scrollTo(0, smoothScroll);
            wheelActive = false;
            wheelFrame = 0;
            document.documentElement.classList.remove('rs-video-scrubbing');
            // Layout start can drift slightly after long custom scrolls.
            metricsValid = false;
            requestScrub();
        }

        function animateWheel() {
            wheelFrame = 0;
            var difference = virtualScroll - smoothScroll;

            if (Math.abs(difference) < 0.4) {
                finishSmoothScroll();
                return;
            }

            // More responsive easing than the original so scroll doesn't "trail"
            // far ahead of the video decoder.
            var ease = navDragging ? 0.16 : 0.18;
            var maxStep = navDragging ? 48 : 64;

            smoothScroll += clamp(difference * ease, -maxStep, maxStep);
            window.scrollTo(0, smoothScroll);
            requestScrub();
            wheelFrame = requestAnimationFrame(animateWheel);
        }

        function moveToProgress(progress) {
            var m = getMetrics();

            if (!wheelActive) {
                smoothScroll = window.pageYOffset || window.scrollY || 0;
                virtualScroll = smoothScroll;
                wheelActive = true;
            }

            document.documentElement.classList.add('rs-video-scrubbing');
            virtualScroll = m.start + clamp(progress, 0, 1) * m.range;

            if (!wheelFrame) {
                wheelFrame = requestAnimationFrame(animateWheel);
            }
        }

        function handleHeroWheel(event) {
            if (
                window.innerWidth <= 780 ||
                event.ctrlKey ||
                Math.abs(event.deltaY) < 1
            ) {
                return;
            }

            var m = getMetrics();
            var current = window.pageYOffset || window.scrollY || 0;
            var inside = current >= m.start - 2 && current <= m.end + 2;

            if (!inside) {
                return;
            }

            if (
                (event.deltaY < 0 && current <= m.start + 1) ||
                (event.deltaY > 0 && current >= m.end - 1)
            ) {
                return;
            }

            event.preventDefault();

            if (!wheelActive) {
                smoothScroll = current;
                virtualScroll = current;
                wheelActive = true;
            }

            document.documentElement.classList.add('rs-video-scrubbing');

            // Slightly stronger wheel mapping for snappier scrub feel.
            virtualScroll = clamp(
                virtualScroll + event.deltaY * 0.85,
                m.start,
                m.end
            );

            if (!wheelFrame) {
                wheelFrame = requestAnimationFrame(animateWheel);
            }
        }

        function progressFromPointer(event) {
            var rect = navTrack.getBoundingClientRect();
            return clamp(
                (event.clientY - rect.top) / Math.max(1, rect.height),
                0,
                1
            );
        }

        navTrack.addEventListener('pointerdown', function (event) {
            event.preventDefault();
            navDragging = true;
            if (navTrack.setPointerCapture) {
                navTrack.setPointerCapture(event.pointerId);
            }
            moveToProgress(progressFromPointer(event));
        });

        navTrack.addEventListener('pointermove', function (event) {
            if (navDragging) {
                moveToProgress(progressFromPointer(event));
            }
        });

        function endDrag(event) {
            if (!navDragging) {
                return;
            }
            navDragging = false;
            if (navTrack.releasePointerCapture) {
                navTrack.releasePointerCapture(event.pointerId);
            }
        }

        navTrack.addEventListener('pointerup', endDrag);
        navTrack.addEventListener('pointercancel', endDrag);

        navTrack.addEventListener('keydown', function (event) {
            var validKeys = [
                'ArrowUp',
                'ArrowDown',
                'PageUp',
                'PageDown',
                'Home',
                'End'
            ];

            if (validKeys.indexOf(event.key) === -1) {
                return;
            }
            event.preventDefault();

            var m = getMetrics();
            var scrollY = window.pageYOffset || window.scrollY || 0;
            var current = clamp((scrollY - m.start) / m.range, 0, 1);
            var jump = event.key.indexOf('Page') === 0 ? 0.1 : 0.025;

            var next;
            if (event.key === 'Home') {
                next = 0;
            } else if (event.key === 'End') {
                next = 1;
            } else if (event.key === 'ArrowUp' || event.key === 'PageUp') {
                next = current - jump;
            } else {
                next = current + jump;
            }

            moveToProgress(next);
        });

        function activateVideo() {
            if (scrollStage.classList.contains('rs-video-ready') && Number.isFinite(video.duration) && video.duration > 0) {
                // already activated; refresh end bound if duration became available
            }

            var mediaDuration = Number.isFinite(video.duration) && video.duration > 0
                ? video.duration
                : duration;

            playbackEnd = Math.min(
                duration - 0.08,
                Math.max(frameStep, mediaDuration - 0.08)
            );

            ensurePaused();

            try {
                video.currentTime = 0;
            } catch (error) { /* ignore */ }

            appliedFrame = 0;
            targetFrame = 0;
            seekInFlight = false;

            scrollStage.classList.add('rs-video-ready');
            metricsValid = false;
            recomputeMetrics();
            requestScrub();
        }

        function primeVideo() {
            var playback;
            try {
                playback = video.play();
            } catch (e) {
                return;
            }

            if (playback && typeof playback.then === 'function') {
                playback
                    .then(function () {
                        pausedEnsured = false;
                        ensurePaused();
                        requestScrub();
                    })
                    .catch(function () { /* ignore */ });
            } else {
                pausedEnsured = false;
                ensurePaused();
            }
        }

        video.muted = true;
        video.defaultMuted = true;
        video.playsInline = true;
        video.preload = 'auto';
        video.controls = false;
        video.loop = false;

        // Disable picture-in-picture / remote playback where supported.
        try {
            video.disablePictureInPicture = true;
        } catch (e) { /* ignore */ }

        video.addEventListener('playing', function () {
            pausedEnsured = false;
            ensurePaused();
        });

        video.addEventListener('seeked', onSeeked, { passive: true });

        // If a seek is aborted / stalled, clear the in-flight lock.
        video.addEventListener('seeking', function () {
            seekInFlight = true;
        }, { passive: true });

        video.addEventListener(
            'error',
            function () {
                scrollStage.classList.add('rs-video-error');
            },
            { once: true }
        );

        document.documentElement.addEventListener(
            'touchstart',
            function () {
                primeVideo();
            },
            { once: true, passive: true }
        );

        if (video.readyState >= 1) {
            activateVideo();
        } else {
            video.addEventListener('loadedmetadata', activateVideo, { once: true });
            video.addEventListener('loadeddata', function () {
                if (!scrollStage.classList.contains('rs-video-ready')) {
                    activateVideo();
                }
            }, { once: true });
        }

        // Warm the decoder a bit: after metadata, nudge one frame forward then back.
        video.addEventListener('canplay', function () {
            ensurePaused();
            requestScrub();
        }, { once: true });

        try {
            video.load();
        } catch (e) { /* ignore */ }

        window.addEventListener('wheel', handleHeroWheel, { passive: false });

        window.addEventListener(
            'scroll',
            function () {
                if (!wheelActive) {
                    virtualScroll = window.pageYOffset || window.scrollY || 0;
                    smoothScroll = virtualScroll;
                }
                requestScrub();
            },
            { passive: true }
        );

        window.addEventListener(
            'resize',
            function () {
                // Debounce metric recompute; resize can fire a lot on mobile chrome.
                window.clearTimeout(resizeTimer);
                resizeTimer = window.setTimeout(function () {
                    metricsValid = false;
                    recomputeMetrics();
                    virtualScroll = window.pageYOffset || window.scrollY || 0;
                    smoothScroll = virtualScroll;
                    requestScrub();
                }, 80);
            },
            { passive: true }
        );

        // Invalidate cached start when fonts/images reflow the page.
        if (typeof ResizeObserver !== 'undefined') {
            var ro = new ResizeObserver(function () {
                metricsValid = false;
            });
            ro.observe(scrollHero);
        }

        recomputeMetrics();
        setSceneIfChanged(0);
        updateNavigatorIfChanged(0);
        requestScrub();
    }

    function findAndInit(context) {
        var scope = context || document;
        var heroes = scope.querySelectorAll
            ? scope.querySelectorAll('.rs-scroll-hero')
            : [];

        for (var i = 0; i < heroes.length; i++) {
            initVideoScrollInstance(heroes[i]);
        }

        var widgets = scope.querySelectorAll
            ? scope.querySelectorAll('.elementor-widget-emha-video-scroll')
            : [];
        for (var j = 0; j < widgets.length; j++) {
            initVideoScrollInstance(widgets[j]);
        }
    }

    var hooksBound = false;

    function boot() {
        findAndInit(document);

        if (!hooksBound && window.elementorFrontend && elementorFrontend.hooks) {
            hooksBound = true;
            elementorFrontend.hooks.addAction(
                'frontend/element_ready/emha-video-scroll.default',
                function ($scope) {
                    var el = $scope && $scope[0] ? $scope[0] : $scope;
                    initVideoScrollInstance(el);
                    findAndInit(el);
                }
            );
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }

    $(function () {
        findAndInit(document);
    });

    $(window).on('elementor/frontend/init', function () {
        boot();
    });

    $(window).on('elementor/popup/show', function () {
        setTimeout(function () {
            findAndInit(document);
        }, 50);
    });
})(jQuery);
