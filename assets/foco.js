(() => {
    const root = document.querySelector('[data-focus-root]');
    if (!root) {
        return;
    }

    const display = root.querySelector('[data-timer-display]');
    const status = root.querySelector('[data-timer-status]');
    const ring = root.querySelector('[data-timer-ring]');
    const startButton = root.querySelector('[data-timer-action="start"]');
    const pauseButton = root.querySelector('[data-timer-action="pause"]');
    const resetButton = root.querySelector('[data-timer-action="reset"]');
    const notes = root.querySelector('[data-focus-notes]');
    const notesStatus = root.querySelector('[data-notes-status]');
    const copyNotesButton = root.querySelector('[data-copy-notes]');
    const clearButton = root.querySelector('[data-focus-clear]');
    const taskInputs = [...root.querySelectorAll('[data-focus-task]')];
    const storageKey = 'lexstudy.focus.v1';
    const presetMinutes = Math.max(10, Number(root.dataset.focusMinutes || 25));
    let totalSeconds = presetMinutes * 60;
    let remainingSeconds = totalSeconds;
    let timerId = null;

    const readState = () => {
        try {
            return JSON.parse(localStorage.getItem(storageKey) || '{}');
        } catch {
            return {};
        }
    };

    const writeState = (patch) => {
        const next = { ...readState(), ...patch };
        localStorage.setItem(storageKey, JSON.stringify(next));
        return next;
    };

    const formatTime = (seconds) => {
        const minutes = Math.floor(seconds / 60).toString().padStart(2, '0');
        const rest = Math.floor(seconds % 60).toString().padStart(2, '0');
        return `${minutes}:${rest}`;
    };

    const renderTimer = () => {
        const progress = 100 - Math.round((remainingSeconds / totalSeconds) * 100);
        display.textContent = formatTime(remainingSeconds);
        ring.style.setProperty('--timer-progress', `${progress}%`);
    };

    const stopTimer = (message = 'Pausa ativa') => {
        window.clearInterval(timerId);
        timerId = null;
        status.textContent = message;
    };

    const startTimer = () => {
        if (timerId) {
            return;
        }
        status.textContent = 'Sessão em curso';
        timerId = window.setInterval(() => {
            remainingSeconds -= 1;
            if (remainingSeconds <= 0) {
                remainingSeconds = 0;
                renderTimer();
                stopTimer('Sessão concluída');
                document.body.classList.remove('is-focus-running');
                return;
            }
            renderTimer();
        }, 1000);
        document.body.classList.add('is-focus-running');
    };

    const resetTimer = (minutes = totalSeconds / 60) => {
        stopTimer('Pronto para começar');
        totalSeconds = Math.max(1, Math.round(minutes)) * 60;
        remainingSeconds = totalSeconds;
        document.body.classList.remove('is-focus-running');
        renderTimer();
    };

    root.querySelectorAll('[data-preset-minutes]').forEach((button) => {
        button.addEventListener('click', () => {
            resetTimer(Number(button.dataset.presetMinutes || 25));
        });
    });

    startButton?.addEventListener('click', startTimer);
    pauseButton?.addEventListener('click', () => {
        stopTimer('Pausa ativa');
        document.body.classList.remove('is-focus-running');
    });
    resetButton?.addEventListener('click', () => resetTimer());

    const saved = readState();
    if (notes && typeof saved.notes === 'string') {
        notes.value = saved.notes;
    }

    const savedTasks = saved.tasks && typeof saved.tasks === 'object' ? saved.tasks : {};
    taskInputs.forEach((input) => {
        input.checked = savedTasks[input.dataset.focusTask] === true;
        input.addEventListener('change', () => {
            const current = readState().tasks || {};
            current[input.dataset.focusTask] = input.checked;
            writeState({ tasks: current });
        });
    });

    notes?.addEventListener('input', () => {
        writeState({ notes: notes.value });
        if (notesStatus) {
            notesStatus.textContent = 'Guardado agora.';
        }
    });

    copyNotesButton?.addEventListener('click', async () => {
        if (!notes) {
            return;
        }
        try {
            await navigator.clipboard.writeText(notes.value);
            if (notesStatus) {
                notesStatus.textContent = 'Notas copiadas.';
            }
        } catch {
            notes.select();
            if (notesStatus) {
                notesStatus.textContent = 'Selecionado para copiar.';
            }
        }
    });

    clearButton?.addEventListener('click', () => {
        taskInputs.forEach((input) => {
            input.checked = false;
        });
        writeState({ tasks: {} });
    });

    document.addEventListener('keydown', (event) => {
        const target = event.target;
        const isWriting = target instanceof HTMLTextAreaElement || target instanceof HTMLInputElement;
        if (isWriting || event.code !== 'Space') {
            return;
        }
        event.preventDefault();
        if (timerId) {
            stopTimer('Pausa ativa');
            document.body.classList.remove('is-focus-running');
        } else {
            startTimer();
        }
    });

    renderTimer();
})();
