import React, { useState } from 'react';

export default function FavoritoButton({ viniloId, isFavorito: initialFavorito }) {
  const [isFavorito, setIsFavorito] = useState(initialFavorito);

  const toggleFavorito = async () => {
    try {
      const response = await fetch(`/favoritos/toggle/${viniloId}`, {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      if (response.ok) {
        const data = await response.json();
        setIsFavorito(data.status === 'added');
        
        if (typeof window.showToast === 'function') {
          window.showToast(data.status === 'added' ? 'Añadido a favoritos' : 'Eliminado de favoritos');
        }
      }
    } catch (error) {
      console.error('Error:', error);
    }
  };

  return (
    <button
      onClick={toggleFavorito}
      id={`fav-btn-${viniloId}`}
      className={`flex h-9 w-9 items-center justify-center rounded-lg border border-surface-dark ${isFavorito ? 'bg-[#e00000]' : 'bg-surface-dark'} hover:bg-[#e00000] transition-colors`}
    >
      <span className="material-symbols-outlined text-white text-base">
        {isFavorito ? 'favorite' : 'favorite_border'}
      </span>
    </button>
  );
}
