(function ($) {
    const initVideoScroll = function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/emha-video-scroll.default', function ($scope) {
            const scrollHero = $scope.find('.rs-scroll-hero')[0];
            if (!scrollHero) return;

            const configAttr = scrollHero.getAttribute('data-emha-config');
            if (!configAttr) return;

            const config = JSON.parse(configAttr);
            const sceneTimes = config.sceneTimes || [0, 5.8, 11.7, 17.6, 23.4];
            const duration = config.duration || 30;
            const sourceFps = config.fps || 15;

            const scrollStage = $scope.find('.rs-scroll-stage')[0];
            const video = $scope.find('.rs-scroll-video')[0];
            const scenes = [...$scope.find('.rs-scroll-scene')];

            const totalFrames = Math.max(1, Math.round(duration * sourceFps));
            const clamp = (value, min, max) => Math.max(min, Math.min(max, value));

            let playbackEnd = duration - 0.08;
            let framePending = false;
            let desiredTime = 0;
            let virtualScroll = window.scrollY;
            let smoothScroll = window.scrollY;
            let wheelFrame = 0;
            let wheelActive = false;
            let navDragging = false;
            let videoNav = null;
            let navTrack = null;
            let navProgress = null;

            if (!scrollStage || !video) return;

            // Create vertical frame navigator.
            videoNav = document.createElement('div');
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
            navTrack = videoNav.querySelector('.rs-video-track');
            navProgress = videoNav.querySelector('.rs-video-progress');

            function heroMetrics() {
                const rect = scrollHero.getBoundingClientRect();
                const start = rect.top + window.scrollY;
                const range = Math.max(1, rect.height - window.innerHeight);
                return { start, range, end: start + range };
            }

            function sceneIndexAt(time) {
                let active = 0;
                for (let i = sceneTimes.length - 1; i >= 0; i--) {
                    if (time >= sceneTimes[i]) {
                        active = i;
                        break;
                    }
                }
                return active;
            }

            function setScene(time) {
                const active = sceneIndexAt(time);
                scenes.forEach((scene, index) => {
                    scene.classList.toggle('rs-scene-active', index === active);
                });
            }

            function updateNavigator(progress) {
                const safeProgress = clamp(progress, 0, 1);
                const percent = (safeProgress * 100).toFixed(3) + '%';
                const frame = Math.min(
                    totalFrames,
                    Math.max(1, Math.round(safeProgress * (totalFrames - 1)) + 1)
                );

                scrollStage.style.setProperty('--scroll-progress', String(safeProgress));
                videoNav.style.setProperty('--video-progress', percent);
                navTrack.setAttribute('aria-valuenow', String(frame));

                if (navProgress) {
                    navProgress.textContent =
                        String(frame).padStart(3, '0') +
                        ' / ' +
                        String(totalFrames).padStart(3, '0');
                }
            }

            function scrubVideo() {
                framePending = false;
                if (video.readyState < 1) return;

                const metrics = heroMetrics();
                const progress = clamp(
                    (window.scrollY - metrics.start) / metrics.range,
                    0,
                    1
                );

                const exactTime = progress * playbackEnd;

                // Snap seeking to the source video's FPS frame intervals.
                desiredTime = Math.min(
                    playbackEnd,
                    Math.round(exactTime * sourceFps) / sourceFps
                );

                updateNavigator(progress);
                setScene(exactTime);

                if (!video.seeking && Math.abs(video.currentTime - desiredTime) > 0.025) {
                    try {
                        video.currentTime = desiredTime;
                    } catch (error) { }
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
                const difference = virtualScroll - smoothScroll;

                if (Math.abs(difference) < 0.28) {
                    finishSmoothScroll();
                    return;
                }

                const ease = navDragging ? 0.085 : 0.095;
                const maxStep = navDragging ? 28 : 40;

                smoothScroll += clamp(difference * ease, -maxStep, maxStep);
                window.scrollTo({ top: smoothScroll, left: 0, behavior: 'auto' });
                requestScrub();
                wheelFrame = requestAnimationFrame(animateWheel);
            }

            function moveToProgress(progress) {
                const metrics = heroMetrics();

                if (!wheelActive) {
                    smoothScroll = window.scrollY;
                    virtualScroll = window.scrollY;
                    wheelActive = true;
                }

                document.documentElement.classList.add('rs-video-scrubbing');
                virtualScroll = metrics.start + clamp(progress, 0, 1) * metrics.range;

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

                const metrics = heroMetrics();
                const current = window.scrollY;
                const inside =
                    current >= metrics.start - 2 &&
                    current <= metrics.end + 2;

                if (!inside) return;

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
                const rect = navTrack.getBoundingClientRect();
                return clamp(
                    (event.clientY - rect.top) / Math.max(1, rect.height),
                    0,
                    1
                );
            }

            navTrack.addEventListener('pointerdown', (event) => {
                event.preventDefault();
                navDragging = true;
                navTrack.setPointerCapture?.(event.pointerId);
                moveToProgress(progressFromPointer(event));
            });

            navTrack.addEventListener('pointermove', (event) => {
                if (navDragging) {
                    moveToProgress(progressFromPointer(event));
                }
            });

            function endDrag(event) {
                if (!navDragging) return;
                navDragging = false;
                navTrack.releasePointerCapture?.(event.pointerId);
            }

            navTrack.addEventListener('pointerup', endDrag);
            navTrack.addEventListener('pointercancel', endDrag);

            navTrack.addEventListener('keydown', (event) => {
                const validKeys = [
                    'ArrowUp',
                    'ArrowDown',
                    'PageUp',
                    'PageDown',
                    'Home',
                    'End'
                ];

                if (!validKeys.includes(event.key)) return;
                event.preventDefault();

                const metrics = heroMetrics();
                const current = clamp(
                    (window.scrollY - metrics.start) / metrics.range,
                    0,
                    1
                );

                const jump = event.key.startsWith('Page') ? 0.1 : 0.025;

                const next =
                    event.key === 'Home'
                        ? 0
                        : event.key === 'End'
                            ? 1
                            : current +
                            (
                                event.key === 'ArrowUp' || event.key === 'PageUp'
                                    ? -jump
                                    : jump
                            );

                moveToProgress(next);
            });

            function activateVideo() {
                playbackEnd = Math.min(
                    duration - 0.08,
                    Math.max(
                        0.1,
                        (Number.isFinite(video.duration) ? video.duration : duration) - 0.08
                    )
                );

                video.pause();

                try {
                    video.currentTime = 0.01;
                } catch (error) { }

                scrollStage.classList.add('rs-video-ready');
                requestScrub();
            }

            function primeVideo() {
                const playback = video.play();

                if (playback && playback.then) {
                    playback
                        .then(() => {
                            video.pause();
                            requestScrub();
                        })
                        .catch(() => { });
                }
            }

            video.muted = true;
            video.defaultMuted = true;
            video.playsInline = true;
            video.preload = 'auto';

            document.documentElement.addEventListener(
                'touchstart',
                primeVideo,
                { once: true, passive: true }
            );

            if (video.readyState >= 1) {
                activateVideo();
            } else {
                video.addEventListener('loadedmetadata', activateVideo, { once: true });
            }

            video.addEventListener(
                'seeked',
                () => {
                    if (Math.abs(video.currentTime - desiredTime) > 0.025) {
                        requestScrub();
                    }
                },
                { passive: true }
            );

            video.addEventListener(
                'error',
                () => scrollStage.classList.add('rs-video-error'),
                { once: true }
            );

            video.load();

            window.addEventListener('wheel', handleHeroWheel, { passive: false });

            window.addEventListener(
                'scroll',
                () => {
                    if (!wheelActive) {
                        virtualScroll = window.scrollY;
                        smoothScroll = window.scrollY;
                    }
                    requestScrub();
                },
                { passive: true }
            );

            window.addEventListener(
                'resize',
                () => {
                    virtualScroll = window.scrollY;
                    smoothScroll = window.scrollY;
                    requestScrub();
                },
                { passive: true }
            );

            setScene(0);
            updateNavigator(0);
            requestScrub();
        });
    };

    if (window.elementorFrontend) {
        initVideoScroll();
    } else {
        $(window).on('elementor/frontend/init', initVideoScroll);
    }
})(jQuery);
