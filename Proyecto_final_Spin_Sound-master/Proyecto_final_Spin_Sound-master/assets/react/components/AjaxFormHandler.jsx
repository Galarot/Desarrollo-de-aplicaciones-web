import React, { useEffect } from 'react';

const MAX_TOASTS = 6;

export default function AjaxFormHandler() {
  useEffect(() => {
    let toastCount = 0;

    const handleSubmit = async (e) => {
      const form = e.target;
      const action = form.action || '';

      if (action.includes('carrito/add') || action.includes('carrito/update') || action.includes('carrito/remove')) {
        e.preventDefault();

        let productName = "Producto";
        if (action.includes('add')) {
          const container = form.closest('article, section, main, div[class*="container"]');
          const titleElement = container ? container.querySelector('h1, h2, h3') : null;
          if (titleElement) productName = titleElement.innerText.trim();
        }

        if (action.includes('add') && toastCount >= MAX_TOASTS) {
          if (typeof window.showToast === 'function') {
            window.showToast('No puedes añadir más productos. Espera a que desaparezcan los avisos.', true);
          }
          return;
        }

        try {
          const response = await fetch(action, {
            method: 'POST',
            body: new FormData(form),
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
          });

          if (response.ok) {
            if (window.location.pathname.includes('/carrito')) {
              window.location.reload();
              return;
            }

            const data = await response.json();
            const badge = document.getElementById('cart-badge');
            let currentCount = parseInt(badge?.innerText || 0);

            if (action.includes('add')) {
              if (typeof window.updateCartBadge === 'function') {
                window.updateCartBadge(currentCount + 1);
              }
              if (badge) {
                badge.style.transform = 'scale(1.5)';
                setTimeout(() => badge.style.transform = 'scale(1)', 200);
              }
              if (typeof window.showToast === 'function') {
                window.showToast(`"${productName}" añadido.`);
                toastCount++;
                setTimeout(() => toastCount--, 4000);
              }
            } else {
              if (typeof window.showToast === 'function') {
                window.showToast('Carrito actualizado.');
              }
            }
          } else {
            const data = await response.json();
            if (data.message && typeof window.showToast === 'function') {
              window.showToast(data.message, true);
            }
          }
        } catch (error) {
          console.error('Error en la petición AJAX:', error);
        }
      }
    };

    document.addEventListener('submit', handleSubmit);
    return () => document.removeEventListener('submit', handleSubmit);
  }, []);

  return null;
}
