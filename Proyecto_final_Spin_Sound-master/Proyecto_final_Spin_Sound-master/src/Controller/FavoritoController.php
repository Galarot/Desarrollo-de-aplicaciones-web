<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FavoritoController extends AbstractController
{
    #[Route('/favoritos/toggle/{viniloId}', name: 'app_favorito_toggle', methods: ['POST'])]
    public function toggle(int $viniloId, EntityManagerInterface $em, Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        $user = $this->getUser();
        $userId = $user->getId();
        
        $qb = $em->createQueryBuilder();
        $favorito = $qb->select('f')
            ->from('App\Entity\Favorito', 'f')
            ->where('f.usuario_id = :userId')
            ->andWhere('f.vinilo_id = :viniloId')
            ->setParameter('userId', $userId)
            ->setParameter('viniloId', $viniloId)
            ->getQuery()
            ->getOneOrNullResult();
        
        if ($favorito) {
            $em->remove($favorito);
            $em->flush();
            
            // Si es AJAX, devolver JSON
            if ($request->isXmlHttpRequest()) {
                return $this->json(['status' => 'removed']);
            }
            // Si no, redirigir de vuelta
            $referer = $request->headers->get('referer');
            return $referer ? $this->redirect($referer) : $this->redirectToRoute('app_favoritos');
        } else {
            $em->getConnection()->executeStatement(
                'INSERT INTO favorito (usuario_id, vinilo_id, created_at) VALUES (:userId, :viniloId, NOW())',
                ['userId' => $userId, 'viniloId' => $viniloId]
            );
            
            // Si es AJAX, devolver JSON
            if ($request->isXmlHttpRequest()) {
                return $this->json(['status' => 'added']);
            }
            // Si no, redirigir de vuelta
            $referer = $request->headers->get('referer');
            return $referer ? $this->redirect($referer) : $this->redirectToRoute('app_favoritos');
        }
    }
    
    #[Route('/favoritos', name: 'app_favoritos', methods: ['GET'])]
    public function index(EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        $userId = $this->getUser()->getId();
        
        $qb = $em->createQueryBuilder();
        $vinilos = $qb->select('v')
            ->from('App\Entity\Vinilo', 'v')
            ->innerJoin('App\Entity\Favorito', 'f', 'WITH', 'v.id = f.vinilo_id')
            ->where('f.usuario_id = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('f.created_at', 'DESC')
            ->getQuery()
            ->getResult();
        
        return $this->render('favorito/index.html.twig', [
            'vinilos' => $vinilos,
        ]);
    }
}
