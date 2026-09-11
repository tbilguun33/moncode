document.addEventListener('click', (event) => {
    const dropdownToggle = event.target.closest('[data-dropdown-toggle]');
    if (dropdownToggle) {
        const menu = dropdownToggle.closest('[data-dropdown]')?.querySelector('[data-dropdown-menu]');
        menu?.classList.toggle('hidden');
    } else {
        document.querySelectorAll('[data-dropdown-menu]').forEach((menu) => {
            if (!menu.closest('[data-dropdown]')?.contains(event.target)) {
                menu.classList.add('hidden');
            }
        });
    }

    if (event.target.closest('[data-mobile-toggle]')) {
        document.querySelector('[data-mobile-menu]')?.classList.toggle('hidden');
    }

    if (event.target.closest('[data-sidebar-toggle]')) {
        document.querySelector('[data-sidebar]')?.classList.toggle('hidden');
    }

    const categoryToggle = event.target.closest('[data-category-toggle]');
    if (categoryToggle) {
        const wrapper = categoryToggle.closest('[data-category-dropdown]');
        wrapper?.querySelector('[data-category-panel]')?.classList.toggle('hidden');
        wrapper?.querySelector('[data-category-chevron]')?.classList.toggle('rotate-180');
    }

    const modalOpen = event.target.closest('[data-modal-open]');
    if (modalOpen) {
        showModal(document.querySelector(`[data-modal="${modalOpen.dataset.modalOpen}"]`));
    }

    if (event.target.closest('[data-modal-close]')) {
        hideModal(event.target.closest('[data-modal]'));
    }

    // Clicking the dimmed backdrop itself (not its content) closes the modal.
    const modalRoot = event.target.closest('[data-modal]');
    if (modalRoot && event.target === modalRoot) {
        hideModal(modalRoot);
    }

    if (event.target.closest('[data-theme-toggle]')) {
        const isDark = document.documentElement.classList.toggle('dark');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
    }

    const copyButton = event.target.closest('[data-copy-code]');
    if (copyButton) {
        const target = copyButton.closest('[data-code-block]')?.querySelector('[data-copy-target]');
        if (target) {
            navigator.clipboard.writeText(target.textContent).then(() => {
                const original = copyButton.textContent;
                copyButton.textContent = 'Хуулсан!';
                setTimeout(() => { copyButton.textContent = original; }, 1500);
            }).catch(() => {
                // Clipboard permission denied or unavailable — fail silently.
            });
        }
    }
});

function showModal(modal) {
    modal?.classList.remove('hidden');
    modal?.classList.add('flex');
}

function hideModal(modal) {
    modal?.classList.add('hidden');
    modal?.classList.remove('flex');
}
