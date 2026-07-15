(() => {
    'use strict';

    const config = window.BibliotecaAudioConfig ?? {};
    const audioBaseUrl = String(config.audioBaseUrl ?? '').replace(/\/$/, '');

    if (audioBaseUrl === '') {
        return;
    }

    const STORAGE = Object.freeze({
        enabled: 'biblioteca.audio.efectos.activos',
        volume: 'biblioteca.audio.efectos.volumen',
    });

    const DEFAULT_VOLUME = 0.36;
    const MAX_POOL_PER_EFFECT = 4;
    const effects = Object.freeze({
        click: 'click.mp3',
        home: 'home.mp3',
        profile: 'perfil-usuario.mp3',
        settings: 'ajustes.mp3',
        popup: 'popUp.mp3',
        error: 'error.mp3',
        loading: 'cargando.mp3',
        data: 'Carga-Datosmultiple.mp3',
    });

    const pools = new Map();
    const state = {
        enabled: readBoolean(STORAGE.enabled, true),
        volume: readNumber(STORAGE.volume, DEFAULT_VOLUME, 0, 0.8),
        lastEffectAt: new Map(),
    };

    let soundButton = null;
    let volumeInput = null;
    let lastPointerTarget = null;
    let lastPointerAt = 0;

    ensureSharedStyles();
    createSoundControls();
    bindInterfaceSounds();
    announceExistingMessages();

    window.WalkMan = Object.freeze({
        play,
        enable: () => setEnabled(true),
        disable: () => setEnabled(false),
        toggle: () => setEnabled(!state.enabled),
        setVolume,
        isEnabled: () => state.enabled,
        effects: Object.keys(effects),
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
            // El audio no depende obligatoriamente de localStorage.
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

    function createSoundControls() {
        const dock = createDock();
        const group = document.createElement('div');
        group.className = 'biblioteca-audio-group biblioteca-sound-group';

        soundButton = document.createElement('button');
        soundButton.type = 'button';
        soundButton.id = 'biblioteca-sound-toggle';
        soundButton.className = 'biblioteca-audio-button';
        soundButton.dataset.audioControl = 'effects';
        soundButton.innerHTML = `
            <span class="biblioteca-audio-glyph" aria-hidden="true">◖))</span>
            <span class="biblioteca-audio-label">Sonidos</span>
            <span class="biblioteca-audio-state" aria-hidden="true"></span>
        `;
        soundButton.addEventListener('click', () => {
            setEnabled(!state.enabled);
        });

        volumeInput = document.createElement('input');
        volumeInput.type = 'range';
        volumeInput.className = 'biblioteca-audio-volume';
        volumeInput.min = '0';
        volumeInput.max = '0.8';
        volumeInput.step = '0.01';
        volumeInput.value = String(state.volume);
        volumeInput.setAttribute('aria-label', 'Volumen de los efectos');
        volumeInput.title = 'Volumen de los efectos';
        volumeInput.addEventListener('input', (event) => {
            setVolume(Number.parseFloat(event.currentTarget.value));
        });

        group.append(soundButton, volumeInput);
        dock.appendChild(group);
        updateSoundButton();
    }

    function setEnabled(enabled) {
        state.enabled = Boolean(enabled);
        writeStorage(STORAGE.enabled, state.enabled);
        updateSoundButton();

        if (state.enabled) {
            void play('popup', { force: true, volumeMultiplier: 0.65 });
        } else {
            stopAllEffects();
        }
    }

    function setVolume(value) {
        const normalized = Number.isFinite(value)
            ? Math.min(0.8, Math.max(0, value))
            : DEFAULT_VOLUME;

        state.volume = normalized;
        writeStorage(STORAGE.volume, normalized);

        if (volumeInput !== null && volumeInput.value !== String(normalized)) {
            volumeInput.value = String(normalized);
        }
    }

    function updateSoundButton() {
        if (soundButton === null) {
            return;
        }

        soundButton.classList.toggle('is-active', state.enabled);
        soundButton.setAttribute('aria-pressed', String(state.enabled));
        soundButton.title = state.enabled
            ? 'Desactivar sonidos de interfaz'
            : 'Activar sonidos de interfaz';
        soundButton.setAttribute('aria-label', soundButton.title);
    }

    function getPool(effectName) {
        if (pools.has(effectName)) {
            return pools.get(effectName);
        }

        const file = effects[effectName];
        if (!file) {
            return [];
        }

        const pool = [];
        pools.set(effectName, pool);
        return pool;
    }

    function obtainAudio(effectName) {
        const pool = getPool(effectName);
        let player = pool.find((item) => item.paused || item.ended);

        if (!player && pool.length < MAX_POOL_PER_EFFECT) {
            player = new Audio(`${audioBaseUrl}/${encodeURIComponent(effects[effectName])}`);
            player.preload = 'auto';
            pool.push(player);
        }

        return player ?? pool[0] ?? null;
    }

    async function play(effectName = 'click', options = {}) {
        const {
            force = false,
            volumeMultiplier = 1,
            cooldown = effectName === 'click' ? 45 : 160,
        } = options;

        if ((!state.enabled && !force) || !effects[effectName]) {
            return false;
        }

        const now = performance.now();
        const previous = state.lastEffectAt.get(effectName) ?? 0;

        if (!force && now - previous < cooldown) {
            return false;
        }

        state.lastEffectAt.set(effectName, now);
        const player = obtainAudio(effectName);

        if (player === null) {
            return false;
        }

        player.pause();
        player.currentTime = 0;
        player.volume = Math.min(1, state.volume * Math.max(0, volumeMultiplier));

        try {
            await player.play();
            return true;
        } catch (error) {
            return false;
        }
    }

    function stopAllEffects() {
        pools.forEach((pool) => {
            pool.forEach((player) => {
                player.pause();
                player.currentTime = 0;
            });
        });
    }

    function bindInterfaceSounds() {
        document.addEventListener('pointerdown', (event) => {
            const target = findInteractiveTarget(event.target);
            lastPointerTarget = target;
            lastPointerAt = performance.now();

            if (target === null || shouldIgnore(target)) {
                return;
            }

            void play(resolveEffect(target));
        }, true);

        document.addEventListener('click', (event) => {
            const target = findInteractiveTarget(event.target);

            if (
                target === null ||
                shouldIgnore(target) ||
                (target === lastPointerTarget && performance.now() - lastPointerAt < 550)
            ) {
                return;
            }

            void play(resolveEffect(target));
        }, true);

        document.addEventListener('submit', (event) => {
            const form = event.target;

            if (!(form instanceof HTMLFormElement) || form.noValidate) {
                return;
            }

            if (!form.checkValidity()) {
                void play('error');
                return;
            }

            const text = normalizedText(form);
            const effect = /excel|export|reporte|import|archivo|carga masiva/.test(text)
                ? 'data'
                : 'loading';

            void play(effect, { volumeMultiplier: effect === 'loading' ? 0.58 : 0.82 });
        }, true);

        document.addEventListener('invalid', () => {
            void play('error');
        }, true);

        document.addEventListener('shown.bs.modal', () => {
            void play('popup');
        });

        document.addEventListener('shown.bs.dropdown', () => {
            void play('popup', { volumeMultiplier: 0.65 });
        });

        document.addEventListener('shown.bs.collapse', () => {
            void play('popup', { volumeMultiplier: 0.55 });
        });

        const observer = new MutationObserver((records) => {
            for (const record of records) {
                for (const node of record.addedNodes) {
                    if (!(node instanceof Element)) {
                        continue;
                    }

                    const alert = node.matches?.('.alert, .admin-alert, [role="alert"]')
                        ? node
                        : node.querySelector?.('.alert, .admin-alert, [role="alert"]');

                    if (alert) {
                        announceAlert(alert);
                        return;
                    }
                }
            }
        });

        if (document.body) {
            observer.observe(document.body, { childList: true, subtree: true });
        }
    }

    function findInteractiveTarget(origin) {
        if (!(origin instanceof Element)) {
            return null;
        }

        return origin.closest(
            'a, button, [role="button"], input[type="submit"], input[type="button"], label[for], select, [data-sound]',
        );
    }

    function shouldIgnore(target) {
        return Boolean(
            target.closest('[data-audio-control]') ||
            target.matches('[disabled], [aria-disabled="true"]') ||
            target.closest('[data-sound="none"]'),
        );
    }

    function resolveEffect(target) {
        const explicit = target.closest('[data-sound]')?.dataset.sound;
        if (explicit && effects[explicit]) {
            return explicit;
        }

        const href = target instanceof HTMLAnchorElement
            ? String(target.getAttribute('href') ?? '').toLowerCase()
            : '';
        const text = normalizedText(target);
        const classNames = String(target.className ?? '').toLowerCase();
        const combined = `${href} ${text} ${classNames}`;

        if (/logout|cerrar sesi[oó]n|eliminar|borrar|cancelar|desactivar/.test(combined)) {
            return 'error';
        }

        if (/portal\/perfil|\/perfil|mi perfil|perfil/.test(combined)) {
            return 'profile';
        }

        if (/dashboard|portal\/inicio|\binicio\b|\bhome\b/.test(combined)) {
            return 'home';
        }

        if (/config|ajuste|permiso|roles|filtro|filtrar|preferencia/.test(combined)) {
            return 'settings';
        }

        if (/excel|export|reporte|import|archivo|carga masiva/.test(combined)) {
            return 'data';
        }

        if (/modal|popup|detalle|ver detalle|data-bs-toggle|desplegar/.test(combined)) {
            return 'popup';
        }

        return 'click';
    }

    function normalizedText(element) {
        return String(
            element.getAttribute?.('aria-label') ??
            element.getAttribute?.('title') ??
            element.textContent ??
            '',
        )
            .trim()
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '');
    }

    function announceExistingMessages() {
        window.setTimeout(() => {
            const visibleAlert = Array.from(
                document.querySelectorAll('.alert, .admin-alert, [role="alert"]'),
            ).find(isVisible);

            if (visibleAlert) {
                announceAlert(visibleAlert);
            }
        }, 280);
    }

    function announceAlert(alert) {
        const descriptor = `${alert.className ?? ''} ${alert.textContent ?? ''}`.toLowerCase();
        const isError = /danger|error|incorrect|bloquead|no se pudo|inv[aá]lid/.test(descriptor);
        void play(isError ? 'error' : 'popup', {
            volumeMultiplier: isError ? 1 : 0.7,
        });
    }

    function isVisible(element) {
        const style = window.getComputedStyle(element);
        return style.display !== 'none' && style.visibility !== 'hidden' && style.opacity !== '0';
    }

    function ensureSharedStyles() {
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

            .biblioteca-audio-glyph {
                display: inline-grid;
                place-items: center;
                width: 21px;
                height: 21px;
                border-radius: 8px;
                background: rgba(255, 255, 255, .34);
                font-size: .78rem;
                line-height: 1;
                white-space: nowrap;
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
