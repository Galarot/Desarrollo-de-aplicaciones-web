<?php

namespace App\EventListener;

use App\Entity\Carrito;
use App\Entity\Cliente;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

#[AsEventListener(event: LoginSuccessEvent::class)]
class LoginSuccessListener
{
    public function __construct(
        private EntityManagerInterface $em,
        private RequestStack $requestStack
    ) {}

    public function __invoke(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();
        
        if (!$user instanceof Cliente) {
            return;
        }

        $session = $this->requestStack->getSession();
        $sessionCartId = $session->get('cart_id');
        
        if (!$sessionCartId) {
            return;
        }

        $sessionCart = $this->em->getRepository(Carrito::class)->find($sessionCartId);
        
        if (!$sessionCart || $sessionCart->getDetalleCarritos()->isEmpty()) {
            $session->remove('cart_id');
            return;
        }

        $userCart = $user->getCarrito();
        
        if (!$userCart) {
            $sessionCart->setCliente($user);
            $this->em->flush();
            $session->remove('cart_id');
            return;
        }

        foreach ($sessionCart->getDetalleCarritos() as $detalle) {
            $vinilo = $detalle->getVinilo();
            $existingDetalle = null;
            
            foreach ($userCart->getDetalleCarritos() as $d) {
                if ($d->getVinilo() && $d->getVinilo()->getId() === $vinilo->getId()) {
                    $existingDetalle = $d;
                    break;
                }
            }
            
            if ($existingDetalle) {
                $existingDetalle->setCantidad($existingDetalle->getCantidad() + $detalle->getCantidad());
            } else {
                $detalle->setCarrito($userCart);
            }
        }

        $this->em->remove($sessionCart);
        $this->em->flush();
        $session->remove('cart_id');
    }
}
