<?php

namespace App\Twig;

use App\Entity\Carrito;
use App\Entity\Cliente;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class CarritoExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private Security               $security,
        private EntityManagerInterface $em,
        private RequestStack           $requestStack,
    ) {}

    public function getGlobals(): array
    {
        $count = 0;

        try {
            $user = $this->security->getUser();

            if ($user instanceof Cliente && $user->getCarrito()) {
                foreach ($user->getCarrito()->getDetalleCarritos() as $detalle) {
                    $count += $detalle->getCantidad() ?? 0;
                }
            } else {
                // usuario anónimo: carrito en sesión
                $session  = $this->requestStack->getSession();
                $cartId   = $session->get('cart_id');
                if ($cartId) {
                    $carrito = $this->em->getRepository(Carrito::class)->find($cartId);
                    if ($carrito) {
                        foreach ($carrito->getDetalleCarritos() as $detalle) {
                            $count += $detalle->getCantidad() ?? 0;
                        }
                    }
                }
            }
        } catch (\Throwable) {
            // Si la BD no está disponible, devolvemos 0 sin romper la página
        }

        return ['carrito_count' => $count];
    }
}

