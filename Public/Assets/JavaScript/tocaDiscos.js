(() => {
    'use strict';

    const config = window.BibliotecaAudioConfig ?? {};
    const audioBaseUrl = String(config.audioBaseUrl ?? '').replace(/\/$/, '');

    if (audioBaseUrl === '') {
        return;
    }

    const STORAGE = Object.freeze({
        enabled: 'biblioteca.audio.musica.activa',
        volume: 'biblioteca.audio.musica.volumen',
        positionPrefix: 'biblioteca.audio.musica.posicion.',
    });

    const DEFAULT_VOLUME = 0.18;
    const FADE_STEP_MS = 45;
    const path = window.location.pathname.replace(/\/+$/, '').toLowerCase();

    const tracks = [
        {
            id: 'admin-home',
            match: /\/dashboard$/,
            file: 'Admin-Home-Music.mp3',
            label: 'Música del panel administrativo',
        },
        {
            id: 'regular-home',
            match: /\/portal\/inicio$/,
            file: 'Regular-Home-Music.mp3',
            label: 'Música del portal académico',
        },
    ];

    const selectedTrack = tracks.find((track) => track.match.test(path)) ?? null;
    const state = {
        enabled: readBoolean(STORAGE.enabled, true),
        volume: readNumber(STORAGE.volume, DEFAULT_VOLUME, 0, 0.65),
        blocked: false,
        fading: false,
    };

    let audio = null;
    let musicButton = null;
    let volumeInput = null;
    let savePositionTimer = 0;

    injectAudioStyles();

    if (selectedTrack !== null) {
        audio = new Audio(`${audioBaseUrl}/${encodeURIComponent(selectedTrack.file)}`);
        audio.loop = true;
        audio.preload = 'metadata';
        audio.volume = 0;
        audio.setAttribute('aria-hidden', 'true');

        createMusicControls();
        restorePosition();
        bindAudioEvents();
        attemptAutomaticPlayback();
    }

    window.TocaDiscos = Object.freeze({
        play: () => playMusic(true),
        pause: () => pauseMusic(true),
        toggle: toggleMusic,
        setVolume,
        isAvailable: () => audio !== null,
        isPlaying: () => audio !== null && !audio.paused,
        isEnabled: () => state.enabled,
        getTrack: () => selectedTrack?.id ?? null,
    });

    function readBoolean(key, fallback) {
        try {
            const stored = window.localStorage.getItem(key);
            return stored === null ? fallback : stored === 'true';
        } catch (error) {
            return fallback;
        }
    }

    function readNumber(key, fallback, minimum, maximum) {
        try {
            const stored = Number.parseFloat(window.localStorage.getItem(key) ?? '');
            return Number.isFinite(stored)
                ? Math.min(maximum, Math.max(minimum, stored))
                : fallback;
        } catch (error) {
            return fallback;
        }
    }

    function writeStorage(key, value) {
        try {
            window.localStorage.setItem(key, String(value));
        } catch (error) {
            // El sitio sigue funcionando aunque el navegador bloquee localStorage.
        }
    }

    function createDock() {
        let dock = document.getElementById('biblioteca-audio-dock');

        if (dock !== null) {
            return dock;
        }

        dock = document.createElement('aside');
        dock.id = 'biblioteca-audio-dock';
        dock.className = 'biblioteca-audio-dock';
        dock.setAttribute('aria-label', 'Controles de audio');
        document.body.appendChild(dock);

        return dock;
    }

    function createMusicControls() {
        const dock = createDock();
        const group = document.createElement('div');
        group.className = 'biblioteca-audio-group biblioteca-music-group';

        musicButton = document.createElement('button');
        musicButton.type = 'button';
        musicButton.id = 'biblioteca-music-toggle';
        musicButton.className = 'biblioteca-audio-button';
        musicButton.dataset.audioControl = 'music';
        musicButton.innerHTML = `
            <span class="biblioteca-audio-glyph" aria-hidden="true">♪</span>
            <span class="biblioteca-audio-label">Música</span>
            <span class="biblioteca-audio-state" aria-hidden="true"></span>
        `;
        musicButton.addEventListener('click', toggleMusic);

        volumeInput = document.createElement('input');
        volumeInput.type = 'range';
        volumeInput.className = 'biblioteca-audio-volume';
        volumeInput.min = '0';
        volumeInput.max = '0.65';
        volumeInput.step = '0.01';
        volumeInput.value = String(state.volume);
        volumeInput.setAttribute('aria-label', 'Volumen de la música');
        volumeInput.title = 'Volumen de la música';
        volumeInput.addEventListener('input', (event) => {
            setVolume(Number.parseFloat(event.currentTarget.value));
        });

        group.append(musicButton, volumeInput);
        dock.appendChild(group);
        updateMusicButton();
    }

    function restorePosition() {
        if (audio === null || selectedTrack === null) {
            return;
        }

        audio.addEventListener('loadedmetadata', () => {
            const saved = readSavedPosition();

            if (Number.isFinite(audio.duration) && saved > 0 && saved < audio.duration - 1) {
                audio.currentTime = saved;
            }
        }, { once: true });
    }

    function savePosition() {
        if (audio === null || selectedTrack === null || !Number.isFinite(audio.currentTime)) {
            return;
        }

        try {
            window.sessionStorage.setItem(
                STORAGE.positionPrefix + selectedTrack.id,
                String(audio.currentTime),
            );
        } catch (error) {
            // La reanudación es opcional.
        }
    }

    function readSavedPosition() {
        if (selectedTrack === null) {
            return 0;
        }

        try {
            const value = Number.parseFloat(
                window.sessionStorage.getItem(STORAGE.positionPrefix + selectedTrack.id) ?? '0',
            );
            return Number.isFinite(value) ? Math.max(0, value) : 0;
        } catch (error) {
            return 0;
        }
    }

    function bindAudioEvents() {
        if (audio === null) {
            return;
        }

        audio.addEventListener('play', () => {
            state.blocked = false;
            updateMusicButton();
            dispatchState();
        });

        audio.addEventListener('pause', () => {
            updateMusicButton();
            dispatchState();
        });

        audio.addEventListener('error', () => {
            state.enabled = false;
            writeStorage(STORAGE.enabled, false);
            updateMusicButton('No se pudo cargar la música');
        });

        audio.addEventListener('timeupdate', () => {
            window.clearTimeout(savePositionTimer);
            savePositionTimer = window.setTimeout(savePosition, 400);
        });

        window.addEventListener('pagehide', savePosition);
        window.addEventListener('beforeunload', savePosition);

        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                savePosition();
                return;
            }

            if (state.enabled && audio.paused) {
                void playMusic(false).catch(() => {});
            }
        });
    }

    function attemptAutomaticPlayback() {
        if (audio === null || !state.enabled) {
            updateMusicButton();
            return;
        }

        const savedPosition = readSavedPosition();
        if (savedPosition > 0) {
            audio.currentTime = savedPosition;
        }

        void playMusic(false).catch(() => {
            state.blocked = true;
            updateMusicButton('Pulsa para iniciar la música');

            const removeUnlockListeners = () => {
                document.removeEventListener('pointerdown', unlock, true);
                document.removeEventListener('keydown', unlock, true);
            };

            const unlock = (event) => {
                if (
                    event.target instanceof Element &&
                    event.target.closest('[data-audio-control]')
                ) {
                    return;
                }

                removeUnlockListeners();

                if (state.enabled && audio?.paused) {
                    void playMusic(false).catch(() => {});
                }
            };

            document.addEventListener('pointerdown', unlock, true);
            document.addEventListener('keydown', unlock, true);
        });
    }

    async function playMusic(fromUser = false) {
        if (audio === null) {
            return;
        }

        state.enabled = true;
        writeStorage(STORAGE.enabled, true);

        try {
            await audio.play();
            state.blocked = false;
            fadeTo(state.volume);
        } catch (error) {
            state.blocked = true;
            updateMusicButton(
                fromUser
                    ? 'El navegador bloqueó la reproducción'
                    : 'Pulsa para iniciar la música',
            );
            throw error;
        }
    }

    function pauseMusic(fromUser = false) {
        if (audio === null) {
            return;
        }

        if (fromUser) {
            state.enabled = false;
            writeStorage(STORAGE.enabled, false);
        }

        fadeTo(0, () => {
            audio?.pause();
            savePosition();
            updateMusicButton();
        });
    }

    function toggleMusic() {
        if (audio === null) {
            return;
        }

        if (!audio.paused && state.enabled) {
            pauseMusic(true);
            return;
        }

        void playMusic(true).catch(() => {});
    }

    function setVolume(value) {
        const normalized = Number.isFinite(value)
            ? Math.min(0.65, Math.max(0, value))
            : DEFAULT_VOLUME;

        state.volume = normalized;
        writeStorage(STORAGE.volume, normalized);

        if (volumeInput !== null && volumeInput.value !== String(normalized)) {
            volumeInput.value = String(normalized);
        }

        if (audio !== null && !audio.paused) {
            audio.volume = normalized;
        }
    }

    function fadeTo(target, callback = null) {
        if (audio === null) {
            callback?.();
            return;
        }

        const destination = Math.min(0.65, Math.max(0, target));
        const difference = destination - audio.volume;

        if (Math.abs(difference) < 0.015) {
            audio.volume = destination;
            callback?.();
            return;
        }

        state.fading = true;
        const step = difference / 12;

        const timer = window.setInterval(() => {
            if (audio === null) {
                window.clearInterval(timer);
                return;
            }

            const next = audio.volume + step;
            const finished = step > 0 ? next >= destination : next <= destination;
            audio.volume = finished ? destination : Math.min(0.65, Math.max(0, next));

            if (finished) {
                window.clearInterval(timer);
                state.fading = false;
                callback?.();
            }
        }, FADE_STEP_MS);
    }

    function updateMusicButton(forcedTitle = '') {
        if (musicButton === null) {
            return;
        }

        const playing = audio !== null && !audio.paused && state.enabled;
        musicButton.classList.toggle('is-active', playing);
        musicButton.classList.toggle('is-blocked', state.blocked);
        musicButton.setAttribute('aria-pressed', String(playing));

        const title = forcedTitle !== ''
            ? forcedTitle
            : playing
                ? `Pausar ${selectedTrack?.label ?? 'música'}`
                : `Reproducir ${selectedTrack?.label ?? 'música'}`;

        musicButton.title = title;
        musicButton.setAttribute('aria-label', title);
    }

    function dispatchState() {
        document.dispatchEvent(new CustomEvent('biblioteca:music-state', {
            detail: {
                playing: audio !== null && !audio.paused,
                enabled: state.enabled,
                track: selectedTrack?.id ?? null,
            },
        }));
    }

    function injectAudioStyles() {
        if (document.getElementById('biblioteca-audio-styles') !== null) {
            return;
        }

        const style = document.createElement('style');
        style.id = 'biblioteca-audio-styles';
        style.textContent = `
            .biblioteca-audio-dock {
                position: fixed;
                right: 18px;
                bottom: 18px;
                z-index: 1085;
                display: flex;
                align-items: center;
                gap: 8px;
                padding: 7px;
                border: 1px solid rgba(109, 60, 28, .18);
                border-radius: 18px;
                background: rgba(255, 250, 242, .94);
                box-shadow: 0 12px 30px rgba(74, 39, 18, .18);
                backdrop-filter: blur(12px);
                -webkit-backdrop-filter: blur(12px);
            }

            .biblioteca-audio-group {
                display: flex;
                align-items: center;
                gap: 7px;
            }

            .biblioteca-audio-group + .biblioteca-audio-group {
                padding-left: 7px;
                border-left: 1px solid rgba(109, 60, 28, .14);
            }

            .biblioteca-audio-button {
                position: relative;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 7px;
                min-height: 38px;
                padding: 8px 12px;
                border: 0;
                border-radius: 12px;
                color: #6d3c1c;
                background: #f8e9d6;
                font: inherit;
                font-size: .82rem;
                font-weight: 800;
                cursor: pointer;
                transition: transform .18s ease, color .18s ease, background .18s ease, box-shadow .18s ease;
            }

            .biblioteca-audio-button:hover,
            .biblioteca-audio-button:focus-visible {
                transform: translateY(-1px);
                color: #fffaf2;
                background: #9b5524;
                box-shadow: 0 7px 15px rgba(155, 85, 36, .22);
                outline: none;
            }

            .biblioteca-audio-button.is-active {
                color: #fffaf2;
                background: linear-gradient(135deg, #6d3c1c, #bf7135);
            }

            .biblioteca-audio-button.is-blocked .biblioteca-audio-state {
                background: #efb521;
            }

            .biblioteca-audio-glyph {
                display: inline-grid;
                place-items: center;
                width: 21px;
                height: 21px;
                border-radius: 8px;
                background: rgba(255, 255, 255, .34);
                font-size: 1.05rem;
                line-height: 1;
            }

            .biblioteca-audio-state {
                width: 7px;
                height: 7px;
                border-radius: 50%;
                background: #a9a096;
                box-shadow: 0 0 0 3px rgba(169, 160, 150, .14);
            }

            .biblioteca-audio-button.is-active .biblioteca-audio-state {
                background: #baf0b4;
                box-shadow: 0 0 0 3px rgba(186, 240, 180, .18);
                animation: biblioteca-audio-pulse 1.5s infinite;
            }

            .biblioteca-audio-volume {
                width: 76px;
                accent-color: #9b5524;
                cursor: pointer;
            }

            @keyframes biblioteca-audio-pulse {
                50% { transform: scale(1.28); }
            }

            @media (max-width: 680px) {
                .biblioteca-audio-dock {
                    right: 10px;
                    bottom: 10px;
                    gap: 5px;
                    padding: 5px;
                }

                .biblioteca-audio-button {
                    min-width: 38px;
                    min-height: 38px;
                    padding: 8px;
                }

                .biblioteca-audio-label,
                .biblioteca-audio-volume {
                    display: none;
                }

                .biblioteca-audio-group + .biblioteca-audio-group {
                    padding-left: 5px;
                }
            }

            @media (prefers-reduced-motion: reduce) {
                .biblioteca-audio-button,
                .biblioteca-audio-state {
                    transition: none !important;
                    animation: none !important;
                }
            }
        `;
        document.head.appendChild(style);
    }
})();
