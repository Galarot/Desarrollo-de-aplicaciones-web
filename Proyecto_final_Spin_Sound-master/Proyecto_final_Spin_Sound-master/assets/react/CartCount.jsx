import React, { useState, useEffect } from 'react';

export default function CartCount({ initial = 0 }) {
  const [count, setCount] = useState(initial);

  useEffect(() => {
    // permitir actualización global via evento CustomEvent
    const handler = (e) => {
      if (e.detail && typeof e.detail.items !== 'undefined') {
        setCount(e.detail.items);
      }
    };
    window.addEventListener('cart-updated', handler);
    return () => window.removeEventListener('cart-updated', handler);
  }, []);



  return (
    <span id="cart-count" className="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full text-xs font-bold text-white" style={{backgroundColor: '#e00000'}} aria-hidden="true">
      {count}
    </span>
  );
}

