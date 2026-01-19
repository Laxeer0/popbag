(() => {
  // Expose header height as a CSS variable so hero sections can center correctly below a sticky header.
  const header = document.querySelector('header');
  const setHeaderHeightVar = () => {
    if (!header) return;
    const h = Math.ceil(header.getBoundingClientRect().height);
    document.documentElement.style.setProperty('--popbag-header-h', `${h}px`);
  };

  setHeaderHeightVar();
  window.addEventListener('resize', setHeaderHeightVar, { passive: true });
  if (window.ResizeObserver && header) {
    new ResizeObserver(setHeaderHeightVar).observe(header);
  }

  const toggles = document.querySelectorAll('[data-popbag-menu-toggle]');
  const panel = document.querySelector('[data-popbag-menu-panel]');
  const backdrop = document.querySelector('[data-popbag-menu-backdrop]');

  if (!toggles.length || !panel) return;

  const setExpanded = (value) => {
    toggles.forEach((t) => t.setAttribute('aria-expanded', value ? 'true' : 'false'));
  };

  const open = () => {
    panel.classList.remove('hidden');
    backdrop?.classList.remove('hidden');
    setExpanded(true);
    document.documentElement.classList.add('overflow-hidden');
  };

  const close = () => {
    panel.classList.add('hidden');
    backdrop?.classList.add('hidden');
    setExpanded(false);
    document.documentElement.classList.remove('overflow-hidden');
  };

  toggles.forEach((t) => {
    t.addEventListener('click', () => {
      const isOpen = panel.classList.contains('hidden') === false;
      isOpen ? close() : open();
    });
  });

  backdrop?.addEventListener('click', close);

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') close();
  });

  // Mobile sub-menu toggles (accordion).
  const bindSubmenuToggles = () => {
    const links = panel?.querySelectorAll('.popbag-mobile-nav li.popbag-has-dropdown > a');
    if (!links || !links.length) return;

    links.forEach((link) => {
      link.addEventListener('click', (e) => {
        const li = link.closest('li');
        if (!li) return;

        const href = (link.getAttribute('href') || '').trim();
        const isRealLink = href !== '' && href !== '#' && !href.toLowerCase().startsWith('javascript:');
        const isOpen = li.classList.contains('is-open');

        // If the parent item has a real link:
        // - first tap opens submenu (no navigation)
        // - second tap navigates to the parent link
        if (isRealLink && !isOpen) {
          e.preventDefault();
          e.stopPropagation();
          li.classList.add('is-open');
          link.setAttribute('aria-expanded', 'true');
          return;
        }

        // If it's not a real link (or you want accordion behavior), toggle open/close.
        if (!isRealLink) {
          e.preventDefault();
          e.stopPropagation();
          const next = li.classList.toggle('is-open');
          link.setAttribute('aria-expanded', next ? 'true' : 'false');
        }
      });
    });
  };

  bindSubmenuToggles();
})();



