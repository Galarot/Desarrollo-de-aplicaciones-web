import React from 'react';
import FavoritoButton from './FavoritoButton';

export default function ViniloCard({ vinilo, isFavorito, csrfToken }) {
  const artista = vinilo.artistas?.[0];
  const genero = vinilo.generos?.[0];

  return (
    <article className="group rounded-xl bg-surface-dark overflow-hidden shadow-lg hover:shadow-primary/20 transition-shadow border border-gray-800">
      <a href={`/vinilo/${vinilo.id}`} className="block">
        <div className="aspect-square overflow-hidden bg-background-dark flex items-center justify-center">
          {vinilo.imagen ? (
            <img src={vinilo.imagen} alt={vinilo.titulo} className="h-full w-full object-cover object-center" />
          ) : (
            <svg fill="currentColor" className="h-16 w-16 text-on-surface-muted-dark" viewBox="0 0 256 256">
              <path d="M128,24A104,104,0,1,0,232,128,104.11,104.11,0,0,0,128,24Zm0,192a88,88,0,1,1,88-88A88.1,88.1,0,0,1,128,216Zm40-96a40,40,0,1,1-40-40A40,40,0,0,1,168,120Zm-16,0a24,24,0,1,0-24,24A24,24,0,0,0,152,120Z"/>
            </svg>
          )}
        </div>
        <div className="p-4">
          <h3 className="font-semibold text-white text-sm leading-snug line-clamp-2">{vinilo.titulo}</h3>
          {artista && (
            <a href={`/vinilos?artista=${artista.id}`} className="mt-1 text-xs text-primary hover:text-[#e00000] hover:underline transition-colors block">
              {artista.nombre}
            </a>
          )}
          {genero && (
            <a href={`/vinilos?genero=${genero.nombre}`} className="mt-1 text-xs text-on-surface-muted-dark hover:text-[#e00000] hover:underline transition-colors block">
              {genero.nombre}
            </a>
          )}
          <p className="mt-1 text-sm font-bold text-white text-right">{vinilo.precio} €</p>
        </div>
      </a>
      <div className="px-4 pb-4 flex items-center gap-2">
        {vinilo.stock > 0 ? (
          <form method="post" action="/carrito/add" className="flex-1">
            <input type="hidden" name="vinilo_id" value={vinilo.id} />
            <input type="hidden" name="_token" value={csrfToken} />
            <button type="submit" className="w-full rounded-lg py-2 text-sm font-bold text-white transition-transform hover:scale-105" style={{ backgroundColor: '#e00000' }}>
              Añadir al carrito
            </button>
          </form>
        ) : (
          <button disabled className="flex-1 rounded-lg py-2 text-sm font-bold text-gray-800 cursor-not-allowed" style={{ backgroundColor: '#fbbf24' }}>
            Sin stock
          </button>
        )}
        <FavoritoButton viniloId={vinilo.id} isFavorito={isFavorito} />
      </div>
    </article>
  );
}
