/**
 * 3D Video Scroll — scroll-scrub engine.
 * Ported from complete-scroll-video-demo.html and adapted for Elementor multi-instance.
 */
(function ($) {
    'use strict';

    function clamp(value, min, max) {
        return Math.max(min, Math.min(max, value));
    }

    /**
     * Document Y of an element (works inside nested Elementor wrappers).
     */
    function documentOffsetTop(el) {
        var top = 0;
        var node = el;
        while (node) {
            top += node.offsetTop || 0;
            node = node.offsetParent;
        }
        // Fallback if offsetParent chain is broken by transforms.
        if (!top) {
            var rect = el.getBoundingClientRect();
            top = rect.top + (window.pageYOffset || window.scrollY || 0);
        }
        return top;
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

        // Mark both widget root and hero so re-inits are skipped.
        root.setAttribute('data-emha-vs-ready', '1');
        scrollHero.setAttribute('data-emha-vs-ready', '1');

        var config = parseConfig(scrollHero);
        var sceneTimes = config.sceneTimes;
        var duration = config.duration;
        var sourceFps = config.fps;

        var scrollStage = scrollHero.querySelector('.rs-scroll-stage');
        var video = scrollHero.querySelector('.rs-scroll-video');
        var scenes = Array.prototype.slice.call(scrollHero.querySelectorAll('.rs-scroll-scene'));

        if (!scrollStage || !video) {
            return;
        }

        var totalFrames = Math.max(1, Math.round(duration * sourceFps));
        var playbackEnd = Math.max(0.1, duration - 0.08);
        var framePending = false;
        var desiredTime = 0;
        var virtualScroll = window.scrollY || window.pageYOffset || 0;
        var smoothScroll = virtualScroll;
        var wheelFrame = 0;
        var wheelActive = false;
        var navDragging = false;

        // Create vertical frame navigator (same as demo).
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

        function heroMetrics() {
            // Match demo intent: use layout position/height of the tall hero track.
            var start = documentOffsetTop(scrollHero);
            var height = scrollHero.offsetHeight || scrollHero.getBoundingClientRect().height;
            var range = Math.max(1, height - window.innerHeight);
            return { start: start, range: range, end: start + range };
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

        function setScene(time) {
            var active = sceneIndexAt(time);
            scenes.forEach(function (scene, index) {
                scene.classList.toggle('rs-scene-active', index === active);
            });
        }

        function updateNavigator(progress) {
            var safeProgress = clamp(progress, 0, 1);
            var percent = (safeProgress * 100).toFixed(3) + '%';
            var frame = Math.min(
                totalFrames,
                Math.max(1, Math.round(safeProgress * (totalFrames - 1)) + 1)
            );

            scrollStage.style.setProperty('--scroll-progress', String(safeProgress));
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

        function scrubVideo() {
            framePending = false;
            if (video.readyState < 1) {
                return;
            }

            // Scroll controls the frame — never free-play.
            if (!video.paused) {
                try {
                    video.pause();
                } catch (e) { /* ignore */ }
            }

            var metrics = heroMetrics();
            var scrollY = window.scrollY || window.pageYOffset || 0;
            var progress = clamp((scrollY - metrics.start) / metrics.range, 0, 1);
            var exactTime = progress * playbackEnd;

            // Snap seeking to source FPS frame intervals (demo: 15fps).
            desiredTime = Math.min(
                playbackEnd,
                Math.round(exactTime * sourceFps) / sourceFps
            );

            updateNavigator(progress);
            setScene(exactTime);

            if (!video.seeking && Math.abs(video.currentTime - desiredTime) > 0.025) {
                try {
                    video.currentTime = desiredTime;
                } catch (error) { /* ignore seek errors */ }
            }
        }

        function requestScrub() {
            if (!framePending) {
                framePending = true;
                requestAnimationFrame(scrubVideo);
            }
        }

        function finishSmoothScroll() {
            smoothScroll = virtualScroll;
            window.scrollTo({ top: smoothScroll, left: 0, behavior: 'auto' });
            wheelActive = false;
            wheelFrame = 0;
            document.documentElement.classList.remove('rs-video-scrubbing');
            requestScrub();
        }

        function animateWheel() {
            wheelFrame = 0;
            var difference = virtualScroll - smoothScroll;

            if (Math.abs(difference) < 0.28) {
                finishSmoothScroll();
                return;
            }

            var ease = navDragging ? 0.085 : 0.095;
            var maxStep = navDragging ? 28 : 40;

            smoothScroll += clamp(difference * ease, -maxStep, maxStep);
            window.scrollTo({ top: smoothScroll, left: 0, behavior: 'auto' });
            requestScrub();
            wheelFrame = requestAnimationFrame(animateWheel);
        }

        function moveToProgress(progress) {
            var metrics = heroMetrics();

            if (!wheelActive) {
                smoothScroll = window.scrollY || window.pageYOffset || 0;
                virtualScroll = smoothScroll;
                wheelActive = true;
            }

            document.documentElement.classList.add('rs-video-scrubbing');
            virtualScroll = metrics.start + clamp(progress, 0, 1) * metrics.range;

            if (!wheelFrame) {
                wheelFrame = requestAnimationFrame(animateWheel);
            }
        }

        function handleHeroWheel(event) {
            // Desktop-only custom wheel scrub (same as demo).
            if (
                window.innerWidth <= 780 ||
                event.ctrlKey ||
                Math.abs(event.deltaY) < 1
            ) {
                return;
            }

            var metrics = heroMetrics();
            var current = window.scrollY || window.pageYOffset || 0;
            var inside =
                current >= metrics.start - 2 &&
                current <= metrics.end + 2;

            if (!inside) {
                return;
            }

            if (
                (event.deltaY < 0 && current <= metrics.start + 1) ||
                (event.deltaY > 0 && current >= metrics.end - 1)
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
            virtualScroll = clamp(
                virtualScroll + event.deltaY * 0.72,
                metrics.start,
                metrics.end
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

            var metrics = heroMetrics();
            var scrollY = window.scrollY || window.pageYOffset || 0;
            var current = clamp((scrollY - metrics.start) / metrics.range, 0, 1);
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
            var mediaDuration = Number.isFinite(video.duration) && video.duration > 0
                ? video.duration
                : duration;

            playbackEnd = Math.min(
                duration - 0.08,
                Math.max(0.1, mediaDuration - 0.08)
            );

            // Demo pattern: ensure media is paused after load — scrub only.
            try {
                video.pause();
            } catch (e) { /* ignore */ }

            try {
                video.currentTime = 0.01;
            } catch (error) { /* ignore */ }

            scrollStage.classList.add('rs-video-ready');
            requestScrub();
        }

        /**
         * Mobile unlock (from demo): brief play → immediate pause so seeking works.
         * Does not leave the video playing.
         */
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
                        try {
                            video.pause();
                        } catch (e2) { /* ignore */ }
                        requestScrub();
                    })
                    .catch(function () { /* autoplay blocked — scrub still works on many browsers */ });
            } else {
                try {
                    video.pause();
                } catch (e3) { /* ignore */ }
            }
        }

        // Match demo media flags.
        video.muted = true;
        video.defaultMuted = true;
        video.playsInline = true;
        video.preload = 'auto';
        video.controls = false;
        video.loop = false;

        // If browser starts autoplay (attribute present), pause as soon as it plays.
        var hasPrimed = false;
        video.addEventListener('playing', function () {
            // Allow only the short mobile prime window; otherwise always pause for scrub mode.
            if (!hasPrimed) {
                try {
                    video.pause();
                } catch (e) { /* ignore */ }
                requestScrub();
            }
        });

        document.documentElement.addEventListener(
            'touchstart',
            function () {
                hasPrimed = true;
                primeVideo();
                // Reset prime flag after microtask so subsequent free-play is blocked.
                setTimeout(function () {
                    hasPrimed = false;
                    try {
                        video.pause();
                    } catch (e) { /* ignore */ }
                    requestScrub();
                }, 120);
            },
            { once: true, passive: true }
        );

        if (video.readyState >= 1) {
            activateVideo();
        } else {
            video.addEventListener('loadedmetadata', activateVideo, { once: true });
            // Some browsers fire canplay instead.
            video.addEventListener('loadeddata', activateVideo, { once: true });
        }

        video.addEventListener(
            'seeked',
            function () {
                if (Math.abs(video.currentTime - desiredTime) > 0.025) {
                    requestScrub();
                }
            },
            { passive: true }
        );

        video.addEventListener(
            'error',
            function () {
                scrollStage.classList.add('rs-video-error');
            },
            { once: true }
        );

        // Kick load (demo does this).
        try {
            video.load();
        } catch (e) { /* ignore */ }

        window.addEventListener('wheel', handleHeroWheel, { passive: false });

        window.addEventListener(
            'scroll',
            function () {
                if (!wheelActive) {
                    virtualScroll = window.scrollY || window.pageYOffset || 0;
                    smoothScroll = virtualScroll;
                }
                requestScrub();
            },
            { passive: true }
        );

        window.addEventListener(
            'resize',
            function () {
                virtualScroll = window.scrollY || window.pageYOffset || 0;
                smoothScroll = virtualScroll;
                requestScrub();
            },
            { passive: true }
        );

        setScene(0);
        updateNavigator(0);
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

        // Elementor widget wrappers (in case hero query is nested oddly).
        var widgets = scope.querySelectorAll
            ? scope.querySelectorAll('.elementor-widget-emha-video-scroll')
            : [];
        for (var j = 0; j < widgets.length; j++) {
            initVideoScrollInstance(widgets[j]);
        }
    }

    function boot() {
        findAndInit(document);

        // Elementor frontend hook (editor + frontend).
        if (window.elementorFrontend && elementorFrontend.hooks) {
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

    // Multiple boot paths so init never depends on a single race.
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

    // Elementor preview re-renders.
    $(window).on('elementor/popup/show', function () {
        setTimeout(function () {
            findAndInit(document);
        }, 50);
    });
})(jQuery);
