<?php

namespace App\Controller;

use App\Entity\Vinilo;
use App\Form\ViniloType;
use App\Repository\ArtistaRepository;
use App\Repository\GeneroRepository;
use App\Repository\ViniloRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/vinilo')]
final class ViniloController extends AbstractController
{
    #[Route(name: 'app_vinilo_index', methods: ['GET'])]
    public function index(
        ViniloRepository  $viniloRepository,
        GeneroRepository  $generoRepository,
        ArtistaRepository $artistaRepository,
        Request           $request,
        EntityManagerInterface $em
    ): Response {
        $query     = $request->query->get('q', '');
        $genero    = $request->query->get('genero', '');
        $artista   = $request->query->get('artista', '');
        $precioMax = $request->query->get('precio_max', '');
        $orden     = $request->query->get('orden', '');

        $vinilos = $viniloRepository->findByFilters(
            $query,
            $genero,
            $artista,
            $precioMax !== '' ? (float) $precioMax : null,
            $orden
        );

        $favoritosIds = [];
        if ($this->getUser()) {
            $userId = $this->getUser()->getId();
            $qb = $em->createQueryBuilder();
            $favoritos = $qb->select('f.vinilo_id')
                ->from('App\\Entity\\Favorito', 'f')
                ->where('f.usuario_id = :userId')
                ->setParameter('userId', $userId)
                ->getQuery()
                ->getResult();
            $favoritosIds = array_column($favoritos, 'vinilo_id');
        }

        return $this->render('vinilo/index.html.twig', [
            'vinilos'   => $vinilos,
            'query'     => $query,
            'genero'    => $genero,
            'artista'   => $artista,
            'precio_max' => $precioMax,
            'orden'     => $orden,
            'generos'   => $generoRepository->findBy([], ['nombre' => 'ASC']),
            'artistas'  => $artistaRepository->findBy([], ['nombre' => 'ASC']),
            'favoritosIds' => $favoritosIds,
        ]);
    }


    #[Route('/new', name: 'app_vinilo_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $vinilo = new Vinilo();
        $form   = $this->createForm(ViniloType::class, $vinilo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($vinilo);
            $entityManager->flush();

            return $this->redirectToRoute('app_vinilo_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('vinilo/new.html.twig', [
            'vinilo' => $vinilo,
            'form'   => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_vinilo_show', requirements: ['id' => '\\d+'], methods: ['GET'])]
    public function show(Vinilo $vinilo, EntityManagerInterface $em): Response
    {
        $isFavorito = false;
        
        if ($this->getUser()) {
            $userId = $this->getUser()->getId();
            $qb = $em->createQueryBuilder();
            $favorito = $qb->select('f')
                ->from('App\\Entity\\Favorito', 'f')
                ->where('f.usuario_id = :userId')
                ->andWhere('f.vinilo_id = :viniloId')
                ->setParameter('userId', $userId)
                ->setParameter('viniloId', $vinilo->getId())
                ->getQuery()
                ->getOneOrNullResult();
            
            $isFavorito = $favorito !== null;
        }
        
        return $this->render('vinilo/show.html.twig', [
            'vinilo' => $vinilo,
            'isFavorito' => $isFavorito,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_vinilo_edit', requirements: ['id' => '\\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Vinilo $vinilo, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ViniloType::class, $vinilo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_panel', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('vinilo/edit.html.twig', [
            'vinilo' => $vinilo,
            'form'   => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_vinilo_delete', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function delete(Request $request, Vinilo $vinilo, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$vinilo->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($vinilo);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_panel', [], Response::HTTP_SEE_OTHER);
    }
}
