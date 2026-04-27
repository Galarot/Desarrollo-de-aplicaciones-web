<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DBLegendleController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('db_legendle/index.html.twig');
    }

    #[Route('/api/characters', name: 'api_characters')]
    public function getCharacters(): Response
    {
        $jsonPath = $this->getParameter('kernel.project_dir') . '/data/characters.json';
        $data = json_decode(file_get_contents($jsonPath), true);
        
        return $this->json($data);
    }

    #[Route('/api/splash', name: 'api_splash')]
    public function getSplash(): Response
    {
        $jsonPath = $this->getParameter('kernel.project_dir') . '/data/splash.json';
        $data = json_decode(file_get_contents($jsonPath), true);
        
        return $this->json($data);
    }

    #[Route('/artcart', name: 'app_artcart')]
    public function artcart(): Response
    {
        return $this->render('db_legendle/artcart.html.twig');
    }
}
