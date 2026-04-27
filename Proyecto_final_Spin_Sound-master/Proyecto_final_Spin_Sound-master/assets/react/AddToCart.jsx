import React from 'react';

export default function AddToCart({ viniloId, csrfToken, productName = 'Producto', disabled = false }) {
  const handleClick = async (e) => {
    e.preventDefault();

    if (disabled) return;

    const formData = new FormData();
    formData.append('vinilo_id', viniloId);
    formData.append('_token', csrfToken);

    try {
      const response = await fetch('/carrito/add', {
        method: 'POST',
        body: formData,
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      if (response.ok) {
        const badge = document.getElementById('cart-badge');
        let currentCount = parseInt(badge?.innerText || 0);
        
        if (typeof window.updateCartBadge === 'function') {
          window.updateCartBadge(currentCount + 1);
        }
        
        if (badge) {
          badge.style.transform = 'scale(1.5)';
          setTimeout(() => badge.style.transform = 'scale(1)', 200);
        }
        
        if (typeof window.showToast === 'function') {
          window.showToast(`"${productName}" añadido.`);
        }
      } else {
        const data = await response.json();
        if (data.message && typeof window.showToast === 'function') {
          window.showToast(data.message, true);
        }
      }
    } catch (error) {
      console.error('Error:', error);
    }
  };

  return (
    <button 
      onClick={handleClick} 
      disabled={disabled}
      className={`w-full rounded-lg py-2 text-sm font-bold text-white transition-transform ${disabled ? 'cursor-not-allowed opacity-50' : 'hover:scale-105'}`} 
      style={{ backgroundColor: disabled ? '#fbbf24' : '#e00000' }}
    >
      {disabled ? 'Sin stock' : 'Añadir al carrito'}
    </button>
  );
}

