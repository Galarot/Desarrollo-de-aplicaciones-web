import React, { useState, useEffect } from 'react';

export default function CartBadge({ initialCount = 0 }) {
  const [count, setCount] = useState(initialCount);

  useEffect(() => {
    const savedCount = localStorage.getItem('spin_sound_cart_count');
    if (savedCount !== null) {
      setCount(parseInt(savedCount));
    }

    window.updateCartBadge = (newCount) => {
      setCount(newCount);
      localStorage.setItem('spin_sound_cart_count', newCount);
    };

    const handlePageShow = () => {
      const savedCount = localStorage.getItem('spin_sound_cart_count');
      if (savedCount !== null) {
        setCount(parseInt(savedCount));
      }
    };

    window.addEventListener('pageshow', handlePageShow);
    return () => window.removeEventListener('pageshow', handlePageShow);
  }, []);

  useEffect(() => {
    localStorage.setItem('spin_sound_cart_count', count);
  }, [count]);

  return (
    <span 
      id="cart-badge" 
      className="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full text-xs font-bold text-white transition-transform duration-200" 
      style={{ backgroundColor: '#e00000' }}
    >
      {count}
    </span>
  );
}
