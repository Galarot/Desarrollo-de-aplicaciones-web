<?php

namespace App\Controller;

use App\Entity\Carrito;
use App\Entity\DetalleCarrito;
use App\Form\CarritoType;
use App\Repository\CarritoRepository;
use App\Repository\ViniloRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/carrito')]
final class CarritoController extends AbstractController
{
    #[Route(name: 'app_carrito_index', methods: ['GET'])]
    public function index(CarritoRepository $carritoRepository): Response
    {
        return $this->render('carrito/index.html.twig', [
            'carritos' => $carritoRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_carrito_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $carrito = new Carrito();
        $form = $this->createForm(CarritoType::class, $carrito);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($carrito);
            $entityManager->flush();

            return $this->redirectToRoute('app_carrito_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('carrito/new.html.twig', [
            'carrito' => $carrito,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_carrito_show', requirements: ['id' => '\\d+'], methods: ['GET'])]
    public function show(Carrito $carrito): Response
    {
        return $this->render('carrito/show.html.twig', [
            'carrito' => $carrito,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_carrito_edit', requirements: ['id' => '\\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Carrito $carrito, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CarritoType::class, $carrito);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_carrito_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('carrito/edit.html.twig', [
            'carrito' => $carrito,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_carrito_delete', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function delete(Request $request, Carrito $carrito, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$carrito->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($carrito);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_carrito_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/demo', name: 'app_carrito_demo', methods: ['GET'])]
    public function demo(): Response
    {
        $carrito = new Carrito();
        return $this->render('carrito/show.html.twig', [
            'carrito' => $carrito,
        ]);
    }

    #[Route('/mi-carrito', name: 'app_carrito_show_user', methods: ['GET'])]
    public function showUserCarrito(Request $request, EntityManagerInterface $em): Response
    {
        $user    = $this->getUser();
        $session = $request->getSession();
        $carrito = null;

        if ($user instanceof \App\Entity\Cliente) {
            $carrito = $user->getCarrito();
        }

        if (!$carrito) {
            $cartId = $session->get('cart_id');
            if ($cartId) {
                $carrito = $em->getRepository(Carrito::class)->find($cartId);
            }
        }

        if (!$carrito) {
            $carrito = new Carrito();
        }

        return $this->render('carrito/show.html.twig', [
            'carrito' => $carrito,
        ]);
    }

    #[Route('/add', name: 'app_carrito_add', methods: ['POST'])]
    public function add(Request $request, ViniloRepository $viniloRepository, EntityManagerInterface $em): Response
    {
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('add-to-cart', $token)) {
            if ($request->isXmlHttpRequest()) {
                return $this->json(['status' => 'error', 'message' => 'Token CSRF inválido'], 400);
            }
            $this->addFlash('error', 'Token CSRF inválido.');
            return $this->redirectToRoute('app_vinilo_index');
        }

        $viniloId = $request->request->get('vinilo_id');
        $vinilo   = $viniloRepository->find($viniloId);
        if (!$vinilo) {
            if ($request->isXmlHttpRequest()) {
                return $this->json(['status' => 'error', 'message' => 'Vinilo no encontrado'], 404);
            }
            $this->addFlash('error', 'Vinilo no encontrado.');
            return $this->redirectToRoute('app_vinilo_index');
        }

        // Verificar stock disponible
        if ($vinilo->getStock() <= 0) {
            if ($request->isXmlHttpRequest()) {
                return $this->json(['status' => 'error', 'message' => 'Producto sin stock disponible'], 400);
            }
            $this->addFlash('error', 'Producto sin stock disponible.');
            $referer = $request->headers->get('referer');
            return $referer ? $this->redirect($referer) : $this->redirectToRoute('app_vinilo_index');
        }

        $user    = $this->getUser();
        $session = $request->getSession();
        $carrito = null;

        if ($user instanceof \App\Entity\Cliente) {
            $carrito = $user->getCarrito();
            if (!$carrito) {
                $carrito = new Carrito();
                $carrito->setCliente($user);
                $em->persist($carrito);
                $em->flush();
            }
        } else {
            $cartId = $session->get('cart_id');
            if ($cartId) {
                $carrito = $em->getRepository(Carrito::class)->find($cartId);
            }
            if (!$carrito) {
                $carrito = new Carrito();
                $em->persist($carrito);
                $em->flush();
                $session->set('cart_id', $carrito->getId());
            }
        }

        $detalle = null;
        foreach ($carrito->getDetalleCarritos() as $d) {
            if ($d->getVinilo() && $d->getVinilo()->getId() === $vinilo->getId()) {
                $detalle = $d;
                break;
            }
        }

        if ($detalle) {
            $nuevaCantidad = ($detalle->getCantidad() ?? 0) + 1;
            if ($nuevaCantidad > $vinilo->getStock()) {
                if ($request->isXmlHttpRequest()) {
                    return $this->json(['status' => 'error', 'message' => 'Stock máximo alcanzado'], 400);
                }
                $this->addFlash('error', 'No hay suficiente stock disponible.');
                $referer = $request->headers->get('referer');
                return $referer ? $this->redirect($referer) : $this->redirectToRoute('app_vinilo_index');
            }
            $detalle->setCantidad($nuevaCantidad);
        } else {
            $detalle = new DetalleCarrito();
            $detalle->setCantidad(1);
            $detalle->setPrecio($vinilo->getPrecio());
            $detalle->setVinilo($vinilo);
            $detalle->setCarrito($carrito);
            $em->persist($detalle);
        }

        $em->flush();

        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'status' => 'success',
                'message' => '"' . $vinilo->getTitulo() . '" añadido al carrito.',
                'cartCount' => count($carrito->getDetalleCarritos())
            ]);
        }

        $this->addFlash('success', '"' . $vinilo->getTitulo() . '" añadido al carrito.');
        $referer = $request->headers->get('referer');
        return $referer ? $this->redirect($referer) : $this->redirectToRoute('app_vinilo_index');
    }


    #[Route('/update-cantidad', name: 'app_carrito_update_cantidad', methods: ['POST'])]
    public function updateCantidad(Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('update-cart', $request->request->get('_token'))) {
            $this->addFlash('error', 'Token inválido.');
            return $this->redirectToRoute('app_carrito_show_user');
        }

        $detalleId = $request->request->get('detalle_id');
        $accion    = $request->request->get('accion');
        $detalle   = $em->getRepository(DetalleCarrito::class)->find($detalleId);

        if ($detalle) {
            $nueva = ($detalle->getCantidad() ?? 1) + ($accion === 'mas' ? 1 : -1);
            
            if ($nueva <= 0) {
                $em->remove($detalle);
            } else {
                // Verificar stock al incrementar
                if ($accion === 'mas') {
                    $vinilo = $detalle->getVinilo();
                    if ($vinilo && $nueva > $vinilo->getStock()) {
                        $this->addFlash('error', 'No hay suficiente stock disponible.');
                        return $this->redirectToRoute('app_carrito_show_user');
                    }
                }
                $detalle->setCantidad($nueva);
            }
            $em->flush();
        }

        return $this->redirectToRoute('app_carrito_show_user');
    }

    #[Route('/remove-item', name: 'app_carrito_remove_item', methods: ['POST'])]
    public function removeItem(Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('remove-cart', $request->request->get('_token'))) {
            $this->addFlash('error', 'Token inválido.');
            return $this->redirectToRoute('app_carrito_show_user');
        }

        $detalleId = $request->request->get('detalle_id');
        $detalle   = $em->getRepository(DetalleCarrito::class)->find($detalleId);

        if ($detalle) {
            $em->remove($detalle);
            $em->flush();
            $this->addFlash('success', 'Producto eliminado del carrito.');
        }

        return $this->redirectToRoute('app_carrito_show_user');
    }
}
