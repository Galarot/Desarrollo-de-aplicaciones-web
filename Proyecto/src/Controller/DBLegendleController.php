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
    // carga la pagina principal del juego
    #[Route('/', name: 'app_home')]
    public function index(CharacterService $characterService): Response
    {
        return $this->render('db_legendle/index.html.twig', [
            'globalConfig' => $characterService->getGlobalConfig(),
        ]);
    }

    // mira si el usu ha completado el desafio de hoy
    #[Route('/api/progress/check', name: 'api_progress_check')]
    public function checkProgress(EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'No autenticado'], 401);
        }

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
            if ($p->isCompleted()) {
                $result[$p->getGameMode()] = true;
            }
        }

        return $this->json($result);
    }

    // guarda que el usu ha ganado y le da cristales
    #[Route('/api/progress/save', name: 'api_progress_save', methods: ['POST'])]
    public function saveProgress(EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'No autenticado'], 401);
        }

        $content = json_decode(file_get_contents('php://input'), true);
        $mode = $content['mode'] ?? null;
        
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
        $em->persist($progress);
        $em->flush();

        if ($rewardGranted) {
            $user->addCrystals(1000);
            
            // da 3000 mas si la racha llega a multiplo de 10
            $streak = $this->calculateStreak($em, $user, $mode);
            if ($streak > 0 && $streak % 10 === 0) {
                $user->addCrystals(3000);
            }
            $em->flush();
        }

        return $this->json([
            'success' => true,
            'rewardGranted' => $rewardGranted,
            'crystals' => $user->getCrystals(),
            'streak' => $this->calculateStreak($em, $user, $mode),
        ]);
    }

    // da cristales por ganar en modo infinito
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

    // saca todos los personajes del json
    #[Route('/api/characters', name: 'api_characters')]
    public function getCharacters(CharacterService $characterService): Response
    {
        $chars = $characterService->getAllCharacters();
        return $this->json($chars);
    }

    // saca todos los splash arts del json
    #[Route('/api/splash', name: 'api_splash')]
    public function getSplash(CharacterService $characterService): Response
    {
        return $this->json($characterService->getAllSplash());
    }

    // carga la vista del modo art cart
    #[Route('/artcart', name: 'app_artcart')]
    public function artcart(): Response
    {
        return $this->render('db_legendle/artcart.html.twig');
    }

    // carga la vista de las invocaciones
    #[Route('/summon', name: 'app_summon')]
    public function summon(ParameterBagInterface $params): Response
    {
        $bannerPath = $params->get('kernel.project_dir') . '/public/assets/multimedia/bannerprueba.png';

        return $this->render('db_legendle/summon.html.twig', [
            'summonBannerVersion' => is_file($bannerPath) ? (int) filemtime($bannerPath) : time(),
        ]);
    }

    // mira los cristales y personajes que tiene el user
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

    // hace una tirada de 10 personajes gastando cristales
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

    // elige un personaje al azar segun su rareza
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

    // calcula cuantos dias seguidos lleva ganando el usu
    private function calculateStreak(EntityManagerInterface $em, object $user, string $mode): int
    {
        $cursor = new \DateTimeImmutable('today');
        $streak = 0;
        $repo = $em->getRepository(DailyProgress::class);

        while (true) {
            $seed = (int) $cursor->format('Ymd');
            $progress = $repo->findOneBy([
                'user' => $user,
                'gameMode' => $mode,
                'seed' => $seed,
                'completed' => true,
            ]);

            if ($progress) {
                $streak++;
                $cursor = $cursor->modify('-1 day');
            } else {
                if ($streak === 0) {
                    $cursor = $cursor->modify('-1 day');
                    $seed = (int) $cursor->format('Ymd');
                    $progress = $repo->findOneBy([
                        'user' => $user,
                        'gameMode' => $mode,
                        'seed' => $seed,
                        'completed' => true,
                    ]);
                    if ($progress) {
                        $streak++;
                        $cursor = $cursor->modify('-1 day');
                        continue;
                    }
                }
                break;
            }
        }

        return $streak;
    }
}
