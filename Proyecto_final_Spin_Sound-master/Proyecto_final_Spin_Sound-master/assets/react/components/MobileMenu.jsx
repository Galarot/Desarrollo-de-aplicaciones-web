import React, { useState } from 'react';

export default function MobileMenu() {
  const [isOpen, setIsOpen] = useState(false);

  return (
    <>
      <button
        onClick={() => setIsOpen(!isOpen)}
        aria-controls="mobile-menu"
        aria-expanded={isOpen}
        className="lg:hidden inline-flex items-center justify-center rounded-md p-2 text-on-surface-muted-light dark:text-on-surface-muted-dark hover:bg-surface-light/20 dark:hover:bg-surface-dark/20 focus:outline-none z-50 relative"
        type="button"
      >
        <span className="sr-only">Abrir menú</span>
        <svg className="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>

      {isOpen && (
        <div id="mobile-menu" className="lg:hidden bg-surface-light dark:bg-surface-dark border-b border-gray-200 dark:border-gray-800">
          <div className="container mx-auto px-4 py-4 space-y-2">
            <a href={window.routes?.app_home || '/'} className="block px-4 py-2 rounded-lg hover:bg-primary/10 transition-colors">Inicio</a>
            <a href={window.routes?.app_vinilo_index || '/vinilos'} className="block px-4 py-2 rounded-lg hover:bg-primary/10 transition-colors">Vinilos</a>
            {window.userRoles?.includes('ROLE_ADMIN') && (
              <a href={window.routes?.app_admin_panel || '/admin'} className="block px-4 py-2 rounded-lg hover:bg-primary/10 transition-colors">Panel Admin</a>
            )}
            {window.userRoles?.includes('ROLE_SUPER_ADMIN') && (
              <a href={window.routes?.app_superadmin_panel || '/superadmin'} className="block px-4 py-2 rounded-lg hover:bg-primary/10 transition-colors">Super Admin</a>
            )}
            {window.isAuthenticated ? (
              <a href={window.routes?.app_logout || '/logout'} className="block px-4 py-2 rounded-lg hover:bg-primary/10 transition-colors">Cerrar sesión</a>
            ) : (
              <>
                <a href={window.routes?.app_login || '/login'} className="block px-4 py-2 rounded-lg hover:bg-primary/10 transition-colors">Iniciar sesión</a>
                <a href={window.routes?.app_register || '/register'} className="block px-4 py-2 rounded-lg hover:bg-primary/10 transition-colors">Registrarse</a>
              </>
            )}
          </div>
        </div>
      )}
    </>
  );
}
