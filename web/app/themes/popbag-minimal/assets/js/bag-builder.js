(() => {
  const modal = document.getElementById("popbag-product-modal");
  if (!modal) return;

  const modalBody = document.getElementById("popbag-product-modal-body");
  const modalTitle = document.getElementById("popbag-product-modal-title");
  const closeBtns = modal.querySelectorAll("[data-popbag-modal-close]");
  const backdrop = modal.querySelector("[data-popbag-modal-backdrop]");

  // Lightbox (image zoom)
  const lightbox = document.getElementById("popbag-lightbox");
  const lightboxImg = document.getElementById("popbag-lightbox-img");
  const lightboxZoomLabel = document.getElementById("popbag-lightbox-zoom");
  const lightboxStage = lightbox ? lightbox.querySelector("[data-popbag-lightbox-stage]") : null;
  const lightboxCloseBtns = lightbox ? lightbox.querySelectorAll("[data-popbag-lightbox-close]") : [];
  const zoomInBtn = lightbox ? lightbox.querySelector("[data-popbag-zoom-in]") : null;
  const zoomOutBtn = lightbox ? lightbox.querySelector("[data-popbag-zoom-out]") : null;
  let lbScale = 1;
  let lbX = 0;
  let lbY = 0;
  let lbDragging = false;
  let lbStartX = 0;
  let lbStartY = 0;
  let lbStartTX = 0;
  let lbStartTY = 0;

  /** @type {number|null} */
  let currentProductId = null;

  const getCheckboxForProduct = (productId) => {
    return document.querySelector(
      `.popbag-product-checkbox[data-product-id="${productId}"]`
    );
  };

  const getCardForProduct = (productId) => {
    return document.querySelector(
      `.popbag-product-card[data-product-id="${productId}"]`
    );
  };

  const syncCardState = (productId) => {
    const checkbox = getCheckboxForProduct(productId);
    const card = getCardForProduct(productId);
    if (!checkbox || !card) return;
    card.classList.toggle("is-selected", !!checkbox.checked);
  };

  const setBodyScrollLocked = (locked) => {
    document.documentElement.style.overflow = locked ? "hidden" : "";
  };

  const updateLightboxZoom = () => {
    if (!lightboxImg) return;
    const pct = Math.round(lbScale * 100);
    if (lightboxZoomLabel) lightboxZoomLabel.textContent = `${pct}%`;
    // translate first so it stays in screen pixels (CSS transforms apply right-to-left)
    lightboxImg.style.transform = `translate(${lbX}px, ${lbY}px) scale(${lbScale})`;
    lightboxImg.classList.toggle("is-zoomed", lbScale > 1);
  };

  const openLightbox = (src, alt = "") => {
    if (!lightbox || !lightboxImg) return;
    lbScale = 1;
    lbX = 0;
    lbY = 0;
    lightboxImg.src = src || "";
    lightboxImg.alt = alt || "";
    updateLightboxZoom();
    lightbox.hidden = false;
    setBodyScrollLocked(true);
  };

  const closeLightbox = () => {
    if (!lightbox || !lightboxImg) return;
    lightbox.hidden = true;
    lightboxImg.src = "";
    lbScale = 1;
    lbX = 0;
    lbY = 0;
    lbDragging = false;
    setBodyScrollLocked(false);
  };

  const openModal = (productId) => {
    const tpl = document.getElementById(`popbag-product-detail-${productId}`);
    if (!tpl || !modalBody) return;

    currentProductId = productId;
    modalBody.innerHTML = "";
    modalBody.appendChild(tpl.content.cloneNode(true));

    const name = tpl.getAttribute("data-product-name") || "";
    if (modalTitle) modalTitle.textContent = name;

    const checkbox = getCheckboxForProduct(productId);
    const selected = !!(checkbox && checkbox.checked);
    const selectBtn = modalBody.querySelector("[data-popbag-modal-select]");
    if (selectBtn) {
      selectBtn.classList.toggle("is-selected", selected);
      selectBtn.textContent = selected ? "Rimuovi" : "Seleziona";
      selectBtn.addEventListener("click", () => {
        if (!currentProductId) return;
        const cb = getCheckboxForProduct(currentProductId);
        if (!cb) return;
        cb.checked = !cb.checked;
        syncCardState(currentProductId);
        const isSel = !!cb.checked;
        selectBtn.classList.toggle("is-selected", isSel);
        selectBtn.textContent = isSel ? "Rimuovi" : "Seleziona";
        closeModal();
      });
    }

    // wire thumbs inside the injected content
    modalBody.querySelectorAll("[data-popbag-thumb]").forEach((btn) => {
      btn.addEventListener("click", () => {
        const url = btn.getAttribute("data-popbag-thumb") || "";
        const main = modalBody.querySelector("[data-popbag-main-image]");
        if (main && url) main.setAttribute("src", url);
      });
    });

    // Lightbox open on main image click (zoom)
    const mainImg = modalBody.querySelector("[data-popbag-main-image]");
    if (mainImg) {
      mainImg.style.cursor = "zoom-in";
      mainImg.addEventListener("click", () => {
        const src = mainImg.getAttribute("src") || "";
        openLightbox(src, name);
      });
    }

    modal.hidden = false;
    setBodyScrollLocked(true);
    // focus close for accessibility
    const close = modal.querySelector(".popbag-modal__close");
    if (close) close.focus();
  };

  const closeModal = () => {
    currentProductId = null;
    if (modalBody) modalBody.innerHTML = "";
    modal.hidden = true;
    setBodyScrollLocked(false);
  };

  // Open modal on card click.
  document.addEventListener("click", (e) => {
    const target = e.target;
    if (!(target instanceof Element)) return;

    const card = target.closest(".popbag-product-card");
    if (!card) return;

    // Clicking the top-right "check hole" toggles selection without opening the modal.
    const toggle = target.closest("[data-popbag-toggle]");
    if (toggle) {
      e.preventDefault();
      const id = Number(card.getAttribute("data-product-id") || "0");
      if (!id) return;
      const checkbox = getCheckboxForProduct(id);
      if (!checkbox) return;
      checkbox.checked = !checkbox.checked;
      syncCardState(id);
      return;
    }

    const id = Number(card.getAttribute("data-product-id") || "0");
    if (!id) return;
    openModal(id);
  });

  // Close interactions.
  closeBtns.forEach((btn) => btn.addEventListener("click", closeModal));
  if (backdrop) backdrop.addEventListener("click", closeModal);
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && lightbox && !lightbox.hidden) closeLightbox();
    else if (e.key === "Escape" && !modal.hidden) closeModal();
  });

  // Lightbox controls
  if (lightbox) {
    lightboxCloseBtns.forEach((btn) => btn.addEventListener("click", closeLightbox));
    if (zoomInBtn) {
      zoomInBtn.addEventListener("click", () => {
        lbScale = Math.min(4, lbScale + 0.25);
        updateLightboxZoom();
      });
    }
    if (zoomOutBtn) {
      zoomOutBtn.addEventListener("click", () => {
        lbScale = Math.max(1, lbScale - 0.25);
        if (lbScale === 1) {
          lbX = 0;
          lbY = 0;
        }
        updateLightboxZoom();
      });
    }
    if (lightboxStage) {
      lightboxStage.addEventListener(
        "wheel",
        (e) => {
          e.preventDefault();
          const dir = e.deltaY > 0 ? -1 : 1;
          lbScale = Math.max(1, Math.min(4, lbScale + dir * 0.15));
          if (lbScale === 1) {
            lbX = 0;
            lbY = 0;
          }
          updateLightboxZoom();
        },
        { passive: false }
      );
    }

    // Drag (grab) to pan when zoomed.
    if (lightboxImg) {
      lightboxImg.addEventListener("pointerdown", (e) => {
        if (lbScale <= 1) return;
        e.preventDefault();
        lbDragging = true;
        lbStartX = e.clientX;
        lbStartY = e.clientY;
        lbStartTX = lbX;
        lbStartTY = lbY;
        lightboxImg.setPointerCapture(e.pointerId);
        lightboxImg.classList.add("is-dragging");
      });

      lightboxImg.addEventListener("pointermove", (e) => {
        if (!lbDragging) return;
        lbX = lbStartTX + (e.clientX - lbStartX);
        lbY = lbStartTY + (e.clientY - lbStartY);
        updateLightboxZoom();
      });

      const endDrag = (e) => {
        if (!lbDragging) return;
        lbDragging = false;
        if (lightboxImg) lightboxImg.classList.remove("is-dragging");
        try {
          if (lightboxImg && e && "pointerId" in e) lightboxImg.releasePointerCapture(e.pointerId);
        } catch (_) {
          // ignore
        }
      };

      lightboxImg.addEventListener("pointerup", endDrag);
      lightboxImg.addEventListener("pointercancel", endDrag);
      lightboxImg.addEventListener("pointerleave", endDrag);
    }
  }

  // Keep card state consistent if checkboxes change elsewhere.
  document.querySelectorAll(".popbag-product-checkbox").forEach((cb) => {
    cb.addEventListener("change", () => {
      const id = Number(cb.getAttribute("data-product-id") || "0");
      if (id) syncCardState(id);
    });
    const id = Number(cb.getAttribute("data-product-id") || "0");
    if (id) syncCardState(id);
  });
})();

