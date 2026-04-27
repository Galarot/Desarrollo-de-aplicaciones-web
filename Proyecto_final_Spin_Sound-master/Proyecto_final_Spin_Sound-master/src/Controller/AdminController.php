<?php

namespace App\Controller;

use App\Entity\Artista;
use App\Entity\Genero;
use App\Entity\Vinilo;
use App\Repository\ArtistaRepository;
use App\Repository\GeneroRepository;
use App\Repository\ViniloRepository;
use App\Service\DiscogsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminController extends AbstractController
{
    #[Route('/admin/panel', name: 'app_admin_panel')]
    public function panel(ViniloRepository $viniloRepository, GeneroRepository $generoRepository, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $busqueda = $request->query->get('busqueda', '');
        $generoFiltro = $request->query->get('genero', '');

        if ($busqueda || $generoFiltro) {
            $vinilos = $viniloRepository->findByFilters($busqueda, $generoFiltro, '', null, '');
        } else {
            $vinilos = $viniloRepository->findAll();
        }

        $novedades = $viniloRepository->findBy(['esNovedad' => true], ['fecha_lanzamiento' => 'DESC']);
        $generos = $generoRepository->findBy([], ['nombre' => 'ASC']);

        return $this->render('admin/panel.html.twig', [
            'vinilos' => $vinilos,
            'novedades' => $novedades,
            'generos' => $generos,
            'busqueda' => $busqueda,
            'generoFiltro' => $generoFiltro,
        ]);
    }

    #[Route('/admin/vinilo/{id}/toggle-novedad', name: 'app_admin_toggle_novedad', methods: ['POST'])]
    public function toggleNovedad(int $id, ViniloRepository $viniloRepository, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $vinilo = $viniloRepository->find($id);
        if (!$vinilo) {
            throw $this->createNotFoundException('Vinilo no encontrado');
        }

        $vinilo->setEsNovedad(!$vinilo->isEsNovedad());
        $em->flush();

        $this->addFlash('success', $vinilo->isEsNovedad()
            ? 'Vinilo añadido a novedades'
            : 'Vinilo quitado de novedades'
        );

        return $this->redirectToRoute('app_admin_panel');
    }

    #[Route('/admin/discogs/search', name: 'app_admin_discogs_search', methods: ['GET'])]
    public function searchDiscogs(Request $request, DiscogsService $discogsService): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $query = $request->query->get('q', '');
        $results = [];
        $error = null;

        if ($query) {
            try {
                $data = $discogsService->search($query, 20);
                $results = $data['results'] ?? [];

                // Si no hay resultados, devolver info útil
                if (empty($results)) {
                    $error = 'No se encontraron resultados. Puede que necesites configurar DISCOGS_TOKEN en .env';
                }
            } catch (\Exception $e) {
                $error = 'Error al buscar en Discogs: ' . $e->getMessage();
            }
        }

        return $this->json([
            'results' => $results,
            'error' => $error,
            'query' => $query
        ]);
    }

    #[Route('/admin/vinilo/{id}/delete', name: 'app_admin_vinilo_delete', methods: ['POST'])]
    public function deleteVinilo(int $id, ViniloRepository $viniloRepository, EntityManagerInterface $em, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if (!$this->isCsrfTokenValid('delete-vinilo' . $id, $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF inválido');
            return $this->redirectToRoute('app_admin_panel');
        }

        $vinilo = $viniloRepository->find($id);
        if (!$vinilo) {
            throw $this->createNotFoundException('Vinilo no encontrado');
        }

        $em->remove($vinilo);
        $em->flush();

        $this->addFlash('success', 'Vinilo eliminado correctamente');
        return $this->redirectToRoute('app_admin_panel');
    }

    #[Route('/admin/vinilos/bulk-update', name: 'app_admin_bulk_update', methods: ['POST'])]
    public function bulkUpdate(Request $request, ViniloRepository $viniloRepository, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if (!$this->isCsrfTokenValid('bulk-update', $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF inválido');
            return $this->redirectToRoute('app_admin_panel');
        }

        $ids = $request->request->all('ids');
        $action = $request->request->get('action');
        $precio = $request->request->get('precio');
        $stock = $request->request->get('stock');

        if (empty($ids)) {
            $this->addFlash('error', 'No se seleccionaron vinilos');
            return $this->redirectToRoute('app_admin_panel');
        }

        $count = 0;
        foreach ($ids as $id) {
            $vinilo = $viniloRepository->find($id);
            if (!$vinilo) continue;

            if ($action === 'delete') {
                $em->remove($vinilo);
                $count++;
            } elseif ($action === 'update') {
                if ($precio !== null && $precio !== '') {
                    $vinilo->setPrecio((float) $precio);
                }
                if ($stock !== null && $stock !== '') {
                    $vinilo->setStock((int) $stock);
                }
                $count++;
            }
        }

        $em->flush();

        $message = $action === 'delete' 
            ? "$count vinilos eliminados" 
            : "$count vinilos actualizados";
        
        $this->addFlash('success', $message);
        return $this->redirectToRoute('app_admin_panel');
    }

    #[Route('/admin/vinilo/add-from-discogs', name: 'app_admin_add_from_discogs', methods: ['POST'])]
    public function addFromDiscogs(
        Request $request,
        DiscogsService $discogsService,
        EntityManagerInterface $em,
        ArtistaRepository $artistaRepository,
        GeneroRepository $generoRepository
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $discogsId = $request->request->get('discogs_id');
        $precio = (float) $request->request->get('precio');
        $stock = (int) $request->request->get('stock');

        if (!$discogsId || $precio <= 0 || $stock < 0) {
            $this->addFlash('error', 'Datos inválidos');
            return $this->redirectToRoute('app_admin_panel');
        }

        try {
            $releaseData = $discogsService->fetchRelease((int) $discogsId);

            if (!$releaseData) {
                $this->addFlash('error', 'No se pudo obtener información del álbum');
                return $this->redirectToRoute('app_admin_panel');
            }

            $vinilo = new Vinilo();
            $vinilo->setTitulo($releaseData['title'] ?? 'Desconocido');
            $vinilo->setPrecio($precio);
            $vinilo->setStock($stock);

            if (!empty($releaseData['year'])) {
                $vinilo->setFechaLanzamiento(new \DateTime($releaseData['year'] . '-01-01'));
            }

            $imageUrl = $discogsService->getImageUrl($releaseData);
            if ($imageUrl) {
                $vinilo->setImagen($imageUrl);
            }

            if (!empty($releaseData['artists'])) {
                foreach ($releaseData['artists'] as $artistData) {
                    $artistaNombre = $artistData['name'] ?? 'Desconocido';
                    $artista = $artistaRepository->findOneBy(['nombre' => $artistaNombre]);
                    if (!$artista) {
                        $artista = new Artista();
                        $artista->setNombre($artistaNombre);
                        $artista->setNacionalidad('Desconocida');
                        $em->persist($artista);
                    }
                    $vinilo->addArtista($artista);
                }
            }

            if (!empty($releaseData['genres'])) {
                $generoNombre = $releaseData['genres'][0];
                $genero = $generoRepository->findOneBy(['nombre' => $generoNombre]);
                if (!$genero) {
                    $genero = new Genero();
                    $genero->setNombre($generoNombre);
                    $em->persist($genero);
                }
                $genero->addGeneroVinilo($vinilo);
            }

            $em->persist($vinilo);
            $em->flush();

            $this->addFlash('success', 'Vinilo añadido correctamente');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error al añadir vinilo: ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_admin_panel');
    }
}
