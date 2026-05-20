<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use App\Entity\DailyProgress;
use App\Service\CharacterService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class DBLegendleController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('db_legendle/index.html.twig');
    }

    #[Route('/api/progress/check', name: 'api_progress_check')]
    public function checkProgress(EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'No autenticado'], 401);
        }

        // Log para debug (ver en logs del servidor o con dump)
        error_log("Comprobando progreso para usuario: " . $user->getUserIdentifier());

        $today = (int)date('Ymd');
        $progress = $em->getRepository(DailyProgress::class)->findBy([
            'user' => $user,
            'seed' => $today
        ]);

        $result = [
            'classic' => false,
            'artcart' => false
        ];

        foreach ($progress as $p) {
            error_log("Encontrado progreso: " . $p->getGameMode() . " completed=" . $p->isCompleted());
            if ($p->isCompleted()) {
                $result[$p->getGameMode()] = true;
            }
        }

        return $this->json($result);
    }

    #[Route('/api/progress/save', name: 'api_progress_save', methods: ['POST'])]
    public function saveProgress(EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'No autenticado'], 401);
        }

        $content = json_decode(file_get_contents('php://input'), true);
        $mode = $content['mode'] ?? null;
        
        error_log("Guardando progreso: mode=$mode, user=" . $user->getUserIdentifier());

        if (!in_array($mode, ['classic', 'artcart'])) {
            return $this->json(['error' => 'Modo inválido'], 400);
        }

        $today = (int)date('Ymd');
        $repo = $em->getRepository(DailyProgress::class);
        $progress = $repo->findOneBy([
            'user' => $user,
            'gameMode' => $mode,
            'seed' => $today
        ]);

        if (!$progress) {
            $progress = new DailyProgress();
            $progress->setUser($user);
            $progress->setGameMode($mode);
            $progress->setSeed($today);
        }

        $progress->setCompleted(true);
        $em->persist($progress);
        $em->flush();

        error_log("Progreso guardado con éxito");

        return $this->json(['success' => true]);
    }

    #[Route('/api/characters', name: 'api_characters')]
    public function getCharacters(CharacterService $characterService): Response
    {
        return $this->json($characterService->getAllCharacters());
    }

    #[Route('/api/splash', name: 'api_splash')]
    public function getSplash(CharacterService $characterService): Response
    {
        return $this->json($characterService->getAllSplash());
    }

    #[Route('/artcart', name: 'app_artcart')]
    public function artcart(): Response
    {
        return $this->render('db_legendle/artcart.html.twig');
    }
}
