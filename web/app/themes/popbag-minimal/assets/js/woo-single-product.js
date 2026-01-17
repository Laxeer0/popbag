(() => {
  const wraps = document.querySelectorAll('[data-popbag-rating-slider-wrap]');
  if (!wraps.length) return;

  wraps.forEach((wrap) => {
    const slider = wrap.querySelector('.popbag-rating-slider');
    const valueEl = wrap.querySelector('.popbag-rating-value');
    const select = wrap.querySelector('#rating');
    const form = wrap.closest('form');

    if (!slider || !select) return;

    const isRequired = wrap.getAttribute('data-required') === '1';

    const setUI = (n) => {
      const v = Number.isFinite(n) ? n : 0;
      if (valueEl) valueEl.textContent = `${v}/5`;
    };

    const syncToSelect = (v) => {
      if (v <= 0) {
        select.value = '';
      } else {
        select.value = String(v);
      }
      select.dispatchEvent(new Event('change', { bubbles: true }));
    };

    // Initialize from existing select (if any).
    const initial = parseInt(select.value || '0', 10);
    slider.value = Number.isFinite(initial) ? String(initial) : '0';
    setUI(parseInt(slider.value, 10) || 0);

    slider.addEventListener('input', () => {
      const v = parseInt(slider.value, 10) || 0;
      setUI(v);
      syncToSelect(v);
    });

    if (form && isRequired) {
      form.addEventListener('submit', (e) => {
        const v = parseInt(slider.value, 10) || 0;
        if (v <= 0) {
          e.preventDefault();
          e.stopPropagation();
          wrap.scrollIntoView({ block: 'center', behavior: 'smooth' });
          wrap.classList.add('popbag-rating-error');
        }
      });
    }
  });
})();

