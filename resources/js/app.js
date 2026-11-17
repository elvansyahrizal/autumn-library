// Keyboard focus containment for dialogs rendered with data-focus-trap.
const focusableSelector = [
    'a[href]',
    'button:not([disabled])',
    'input:not([disabled]):not([type="hidden"])',
    'select:not([disabled])',
    'textarea:not([disabled])',
    '[tabindex]:not([tabindex="-1"])',
].join(',');

const activeTrap = () => {
    const traps = Array.from(document.querySelectorAll('[data-focus-trap]'));

    return traps.filter((element) => element.offsetParent !== null).pop() ?? null;
};

document.addEventListener('keydown', (event) => {
    if (event.key !== 'Tab') {
        return;
    }

    const trap = activeTrap();

    if (!trap) {
        return;
    }

    const focusable = Array.from(trap.querySelectorAll(focusableSelector)).filter(
        (element) => element.offsetParent !== null,
    );

    if (focusable.length === 0) {
        event.preventDefault();
        trap.focus();

        return;
    }

    const first = focusable[0];
    const last = focusable[focusable.length - 1];

    if (event.shiftKey && (document.activeElement === first || document.activeElement === trap)) {
        event.preventDefault();
        last.focus();

        return;
    }

    if (! event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
    }
});

// Keep the page from scrolling behind an open dialog.
const syncScrollLock = () => {
    document.body.classList.toggle('dialog-open', activeTrap() !== null);
};

document.addEventListener('DOMContentLoaded', () => {
    syncScrollLock();

    new MutationObserver(syncScrollLock).observe(document.body, {
        childList: true,
        subtree: true,
    });
});
