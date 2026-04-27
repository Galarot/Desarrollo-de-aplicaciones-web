import React from 'react';
import ViniloCard from './ViniloCard';

export default function ViniloList({ vinilos, favoritosIds = [], csrfToken }) {
  if (!vinilos || vinilos.length === 0) {
    return (
      <div className="flex flex-col items-center justify-center py-24 text-center">
        <svg fill="currentColor" className="h-16 w-16 text-on-surface-muted-dark mb-4" viewBox="0 0 256 256">
          <path d="M128,24A104,104,0,1,0,232,128,104.11,104.11,0,0,0,128,24Zm0,192a88,88,0,1,1,88-88A88.1,88.1,0,0,1,128,216Zm40-96a40,40,0,1,1-40-40A40,40,0,0,1,168,120Zm-16,0a24,24,0,1,0-24,24A24,24,0,0,0,152,120Z"/>
        </svg>
        <p className="text-on-surface-muted-dark text-lg">No se encontraron vinilos</p>
        <a href="/vinilos" className="mt-4 text-primary hover:underline text-sm">
          Ver todo el catálogo
        </a>
      </div>
    );
  }

  return (
    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      {vinilos.map(vinilo => (
        <ViniloCard 
          key={vinilo.id} 
          vinilo={vinilo} 
          isFavorito={favoritosIds.includes(vinilo.id)}
          csrfToken={csrfToken}
        />
      ))}
    </div>
  );
}
