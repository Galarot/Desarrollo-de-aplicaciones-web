<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\PedidoRepository;
use App\Repository\CarritoRepository;
use App\Entity\Pedido;
use App\Entity\DetallePedido;
use App\Entity\Cliente;
use App\Entity\Carrito;
use Doctrine\ORM\EntityManagerInterface;

class PedidoController extends AbstractController
{
    #[Route('/pedido/procesar', name: 'app_pedido_procesar', methods: ['POST'])]
    public function procesarPago(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        if (!$this->isCsrfTokenValid('process-payment', $request->request->get('_token'))) {
            $this->addFlash('error', 'Token de seguridad inválido');
            return $this->redirectToRoute('app_carrito_show_user');
        }
        
        $user = $this->getUser();
        $session = $request->getSession();
        
        // Obtener carrito
        $carrito = null;
        if ($user instanceof Cliente && $user->getCarrito()) {
            $carrito = $user->getCarrito();
        } else {
            $cartId = $session->get('cart_id');
            if ($cartId) {
                $carrito = $em->getRepository(Carrito::class)->find($cartId);
            }
        }
        
        if (!$carrito || $carrito->getDetalleCarritos()->isEmpty()) {
            $this->addFlash('error', 'El carrito está vacío');
            return $this->redirectToRoute('app_carrito_show_user');
        }
        
        try {
            // Crear o buscar cliente
            $cliente = null;
            if ($user instanceof Cliente) {
                $cliente = $user;
            } else {
                // Buscar cliente existente
                $cliente = $em->getRepository(Cliente::class)->findOneBy(['email' => $user->getUserIdentifier() . '_cliente']);
                
                if (!$cliente) {
                    // Crear nuevo cliente solo si no existe
                    $cliente = new Cliente();
                    $cliente->setEmail($user->getUserIdentifier() . '_cliente');
                    $cliente->setPassword($user->getPassword());
                    $cliente->setNombre($user->getNombre());
                    $cliente->setDireccion($user->getDireccion());
                    $cliente->setTelefono($user->getTelefono());
                    $cliente->setRoles(['ROLE_USER']);
                    $cliente->setCreatedAt(new \DateTimeImmutable());
                    
                    $nuevoCarrito = new Carrito();
                    $cliente->setCarrito($nuevoCarrito);
                    $nuevoCarrito->setCliente($cliente);
                    
                    $em->persist($nuevoCarrito);
                    $em->persist($cliente);
                    $em->flush();
                }
            }
            
            // Crear pedido
            $pedido = new Pedido();
            $pedido->setCliente($cliente);
            $pedido->setFecha(new \DateTimeImmutable());
            
            $total = 0;
            foreach ($carrito->getDetalleCarritos() as $detalleCarrito) {
                $vinilo = $detalleCarrito->getVinilo();
                $cantidad = $detalleCarrito->getCantidad();
                
                // Verificar stock disponible
                if ($vinilo->getStock() < $cantidad) {
                    $this->addFlash('error', 'Stock insuficiente para ' . $vinilo->getTitulo());
                    return $this->redirectToRoute('app_carrito_show_user');
                }
                
                $detallePedido = new DetallePedido();
                $detallePedido->setPedido($pedido);
                $detallePedido->setVinilo($vinilo);
                $detallePedido->setCantidad($cantidad);
                $detallePedido->setPrecio($detalleCarrito->getPrecio());
                
                $total += $detalleCarrito->getPrecio() * $cantidad;
                
                // Descontar stock
                $vinilo->setStock($vinilo->getStock() - $cantidad);
                
                $em->persist($detallePedido);
                $pedido->addDetalle($detallePedido);
            }
            
            $total += 5.00;
            $pedido->setTotal($total);
            $em->persist($pedido);
            
            foreach ($carrito->getDetalleCarritos() as $detalle) {
                $em->remove($detalle);
            }
            
            $em->flush();
            
            $this->addFlash('success', 'Pedido procesado correctamente');
            return $this->redirectToRoute('app_pedido_mis_pedidos');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error al procesar el pedido: ' . $e->getMessage());
            return $this->redirectToRoute('app_carrito_show_user');
        }
    }

    #[Route('/mis-pedidos', name: 'app_pedido_mis_pedidos')]
    public function misPedidos(PedidoRepository $pedidoRepository, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        $user = $this->getUser();
        $pedidos = [];
        
        if ($user instanceof Cliente) {
            $pedidos = $pedidoRepository->findBy(['cliente' => $user], ['fecha' => 'DESC']);
        } else {
            $cliente = $em->getRepository(Cliente::class)->findOneBy(['email' => $user->getUserIdentifier() . '_cliente']);
            if ($cliente) {
                $pedidos = $pedidoRepository->findBy(['cliente' => $cliente], ['fecha' => 'DESC']);
            }
        }
        
        return $this->render('pedido/mis_pedidos.html.twig', [
            'pedidos' => $pedidos,
        ]);
    }
}
