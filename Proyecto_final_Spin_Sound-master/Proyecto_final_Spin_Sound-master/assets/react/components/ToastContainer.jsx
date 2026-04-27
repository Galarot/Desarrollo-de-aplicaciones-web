import React, { useState, useEffect } from 'react';

const MAX_TOASTS = 6;

export default function ToastContainer() {
  const [toasts, setToasts] = useState([]);

  useEffect(() => {
    window.showToast = (message, isError = false) => {
      if (toasts.length >= MAX_TOASTS) return;

      const id = Date.now();
      const newToast = { id, message, isError };
      
      setToasts(prev => [...prev, newToast]);

      setTimeout(() => {
        setToasts(prev => prev.filter(t => t.id !== id));
      }, 4000);
    };
  }, [toasts.length]);

  return (
    <div id="toast-container" className="fixed bottom-5 right-5 z-[100] flex flex-col gap-3 pointer-events-none">
      {toasts.map(toast => (
        <Toast key={toast.id} message={toast.message} isError={toast.isError} />
      ))}
    </div>
  );
}

function Toast({ message, isError }) {
  const [visible, setVisible] = useState(false);

  useEffect(() => {
    setTimeout(() => setVisible(true), 100);
  }, []);

  const borderColor = isError ? 'border-yellow-500' : 'border-[#e00000]';
  const iconColor = isError ? 'text-yellow-500' : 'text-[#e00000]';
  const icon = isError ? 'warning' : 'check_circle';
  const title = isError ? '¡Límite alcanzado!' : '¡Actualizado!';

  return (
    <div className={`flex items-center gap-3 bg-[#1a1f23] border-l-4 ${borderColor} text-white px-6 py-4 rounded-lg shadow-2xl transition-all duration-500 ease-out max-w-sm w-auto pointer-events-auto ${visible ? 'translate-x-0 opacity-100' : 'translate-x-full opacity-0'}`}>
      <span className={`material-symbols-outlined ${iconColor}`}>{icon}</span>
      <div className="flex flex-col">
        <p className="font-bold text-sm tracking-wide">{title}</p>
        <p className="text-xs text-[#a9aeb2]">{message}</p>
      </div>
    </div>
  );
}
