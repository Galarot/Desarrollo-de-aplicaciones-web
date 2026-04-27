import React, { useState } from 'react';

export default function SearchForm({ initialQuery = '', action = '/vinilos' }) {
  const [query, setQuery] = useState(initialQuery);

  const handleSubmit = (e) => {
    e.preventDefault();
    window.location.href = `${action}?q=${encodeURIComponent(query)}`;
  };

  return (
    <form onSubmit={handleSubmit} className="relative flex-1" id="search-form">
      <label htmlFor="global_search" className="sr-only">Buscar</label>
      <input
        id="global_search"
        name="q"
        value={query}
        onChange={(e) => setQuery(e.target.value)}
        className="h-10 w-full max-w-xs rounded-full border-0 border-gray-600 bg-surface-light dark:bg-surface-dark pl-10 pr-4 text-sm text-on-surface-light dark:text-on-surface-dark placeholder:text-on-surface-muted-light dark:placeholder:text-on-surface-muted-dark focus:outline-none focus:ring-2 focus:ring-rojo outline-none"
        placeholder="Buscar..."
        style={{ '--tw-ring-color': '#e00000' }}
      />
      <button type="submit" className="absolute inset-y-0 left-0 flex items-center pl-3 text-on-surface-muted-light dark:text-on-surface-muted-dark hover:text-rojo transition-colors">
        <svg fill="currentColor" height="20" viewBox="0 0 256 256" width="20" xmlns="http://www.w3.org/2000/svg">
          <path d="M229.66,218.34l-50.07-50.06a88.11,88.11,0,1,0-11.31,11.31l50.06,50.07a8,8,0,0,0,11.32-11.32ZM40,112a72,72,0,1,1,72,72A72.08,72.08,0,0,1,40,112Z"></path>
        </svg>
      </button>
    </form>
  );
}
