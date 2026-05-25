<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use App\Entity\DailyProgress;
use App\Service\CharacterService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
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
            'artcart' => false,
            'streaks' => [
                'classic' => $this->calculateStreak($em, $user, 'classic'),
                'artcart' => $this->calculateStreak($em, $user, 'artcart'),
            ],
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

        $rewardGranted = !$progress->isCompleted();
        $progress->setCompleted(true);
        if ($rewardGranted) {
            $user->addCrystals(1000);
        }
        $em->persist($progress);
        $em->flush();

        error_log("Progreso guardado con éxito");

        return $this->json([
            'success' => true,
            'rewardGranted' => $rewardGranted,
            'crystals' => $user->getCrystals(),
            'streak' => $this->calculateStreak($em, $user, $mode),
        ]);
    }

    #[Route('/api/rewards/infinite', name: 'api_rewards_infinite', methods: ['POST'])]
    public function claimInfiniteReward(EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'No autenticado'], 401);
        }

        $user->addCrystals(500);
        $em->flush();

        return $this->json([
            'success' => true,
            'rewardGranted' => true,
            'crystals' => $user->getCrystals(),
        ]);
    }

    #[Route('/api/characters', name: 'api_characters')]
    public function getCharacters(CharacterService $characterService): Response
    {
        $chars = $characterService->getAllCharacters();
        error_log("API Characters: cargados " . count($chars) . " personajes");
        return $this->json($chars);
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

    #[Route('/summon', name: 'app_summon')]
    public function summon(ParameterBagInterface $params): Response
    {
        $bannerPath = $params->get('kernel.project_dir') . '/public/assets/multimedia/bannerprueba.png';

        return $this->render('db_legendle/summon.html.twig', [
            'summonBannerVersion' => is_file($bannerPath) ? (int) filemtime($bannerPath) : time(),
        ]);
    }

    #[Route('/api/summon/status', name: 'api_summon_status')]
    public function summonStatus(CharacterService $characterService): JsonResponse
    {
        $user = $this->getUser();

        return $this->json([
            'authenticated' => (bool) $user,
            'crystals' => $user ? $user->getCrystals() : 0,
            'ownedCharacterIds' => $user ? $user->getOwnedCharacters() : [],
            'characters' => $characterService->getAllCharacters(),
        ]);
    }

    #[Route('/api/summon/pull', name: 'api_summon_pull', methods: ['POST'])]
    public function summonPull(CharacterService $characterService, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Inicia sesion para hacer invocaciones.'], 401);
        }

        if (!$user->spendCrystals(1000)) {
            return $this->json([
                'error' => 'No tienes cristales suficientes. Completa desafios diarios o modo infinito primero.',
                'crystals' => $user->getCrystals(),
            ], 400);
        }

        $characters = $characterService->getAllCharacters();
        $results = [];
        for ($i = 0; $i < 10; $i++) {
            $character = $this->pickWeightedCharacter($characters);
            if ($character) {
                $results[] = $character;
                $user->addOwnedCharacter((int) $character['id']);
            }
        }

        $em->flush();

        return $this->json([
            'success' => true,
            'crystals' => $user->getCrystals(),
            'ownedCharacterIds' => $user->getOwnedCharacters(),
            'results' => $results,
        ]);
    }

    private function pickWeightedCharacter(array $characters): ?array
    {
        $rarityWeights = [
            'hero' => 40,
            'extreme' => 25,
            'sparking' => 20,
            'lf' => 8,
            'legend' => 5,
            'ultra' => 2,
        ];

        $roll = random_int(1, 100);
        $cursor = 0;
        $selectedRarity = 'hero';
        foreach ($rarityWeights as $rarity => $weight) {
            $cursor += $weight;
            if ($roll <= $cursor) {
                $selectedRarity = $rarity;
                break;
            }
        }

        $pool = array_values(array_filter($characters, static function (array $character) use ($selectedRarity): bool {
            $rarity = strtolower((string) ($character['atributos']['rareza'] ?? ''));

            return $rarity === $selectedRarity;
        }));

        if (!$pool) {
            $pool = $characters;
        }

        if (!$pool) {
            return null;
        }

        return $pool[array_rand($pool)];
    }

    private function calculateStreak(EntityManagerInterface $em, object $user, string $mode): int
    {
        $progressRows = $em->getRepository(DailyProgress::class)->findBy([
            'user' => $user,
            'gameMode' => $mode,
            'completed' => true,
        ]);

        $completedSeeds = [];
        foreach ($progressRows as $progress) {
            $completedSeeds[$progress->getSeed()] = true;
        }

        $cursor = new \DateTimeImmutable('today');
        if (!isset($completedSeeds[(int) $cursor->format('Ymd')])) {
            $cursor = $cursor->modify('-1 day');
        }

        $streak = 0;
        while (isset($completedSeeds[(int) $cursor->format('Ymd')])) {
            $streak++;
            $cursor = $cursor->modify('-1 day');
        }

        return $streak;
    }
}
