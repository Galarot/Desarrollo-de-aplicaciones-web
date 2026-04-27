import React from 'react';
import MobileMenu from './components/MobileMenu';
import ToastContainer from './components/ToastContainer';
import CartBadge from './components/CartBadge';
import AjaxFormHandler from './components/AjaxFormHandler';

export default function App({ cartCount = 0, flashMessages = [] }) {
  return (
    <>
      <ToastContainer />
      <AjaxFormHandler />
      {flashMessages.length > 0 && (
        <FlashMessages messages={flashMessages} />
      )}
    </>
  );
}

function FlashMessages({ messages }) {
  React.useEffect(() => {
    messages.forEach(message => {
      if (typeof window.showToast === 'function') {
        window.showToast(message);
      }
    });
  }, [messages]);

  return null;
}

export { MobileMenu, CartBadge, ToastContainer, AjaxFormHandler };
