<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/users', name: 'app_admin_users')]
    public function index(EntityManagerInterface $em, ParameterBagInterface $params): Response
    {
        $users = $em->getRepository(User::class)->findAll();

        return $this->render('admin/users.html.twig', [
            'users' => $users,
            'characters' => $this->loadJson($params, 'characters.json'),
            'splashCharacters' => $this->loadJson($params, 'splash.json'),
        ]);
    }

    #[Route('/users/{id}/permissions', name: 'app_admin_user_permissions', methods: ['POST'])]
    public function updateUserPermissions(User $user, Request $request, EntityManagerInterface $em): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('admin_user_' . $user->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF no valido.');
        }

        $roles = [];
        if ($request->request->has('role_admin')) {
            $roles[] = 'ROLE_ADMIN';
        }

        $user->setRoles($roles);
        $user->setBanned($request->request->has('banned'));
        $em->flush();

        $this->addFlash('success', 'Permisos de usuario actualizados.');

        return $this->redirectToRoute('app_admin_users');
    }

    #[Route('/characters/save', name: 'app_admin_character_save', methods: ['POST'])]
    public function saveCharacter(Request $request, ParameterBagInterface $params): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('admin_character_save', (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF no valido.');
        }

        $characters = $this->loadJson($params, 'characters.json');
        $id = $this->resolveJsonId($characters, $request->request->get('id'));

        $character = [
            'id' => $id,
            'nombre' => trim((string) $request->request->get('nombre')),
            'anio' => (int) $request->request->get('anio'),
            'art_cart_url' => trim((string) $request->request->get('art_cart_url')),
            'atributos' => [
                'genero' => trim((string) $request->request->get('genero')),
                'afinidad' => trim((string) $request->request->get('afinidad')),
                'rareza' => trim((string) $request->request->get('rareza')),
                'estilo' => trim((string) $request->request->get('estilo')),
                'zenkai' => trim((string) $request->request->get('zenkai')),
                'saga' => trim((string) $request->request->get('saga')),
                'raza' => trim((string) $request->request->get('raza')),
            ],
        ];

        $this->upsertJsonRow($characters, $character);
        $this->saveJson($params, 'characters.json', $characters);
        $this->addFlash('success', 'Personaje guardado en characters.json.');

        return $this->redirectToRoute('app_admin_users');
    }

    #[Route('/splash/save', name: 'app_admin_splash_save', methods: ['POST'])]
    public function saveSplash(Request $request, ParameterBagInterface $params): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('admin_splash_save', (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF no valido.');
        }

        $splashCharacters = $this->loadJson($params, 'splash.json');
        $id = $this->resolveJsonId($splashCharacters, $request->request->get('id'));

        $splash = [
            'id' => $id,
            'nombre' => trim((string) $request->request->get('nombre')),
            'art_url' => trim((string) $request->request->get('art_url')),
        ];

        $this->upsertJsonRow($splashCharacters, $splash);
        $this->saveJson($params, 'splash.json', $splashCharacters);
        $this->addFlash('success', 'Personaje guardado en splash.json.');

        return $this->redirectToRoute('app_admin_users');
    }

    private function loadJson(ParameterBagInterface $params, string $fileName): array
    {
        $path = $this->getDataPath($params, $fileName);
        if (!is_file($path)) {
            return [];
        }

        $data = json_decode((string) file_get_contents($path), true);

        return is_array($data) ? $data : [];
    }

    private function saveJson(ParameterBagInterface $params, string $fileName, array $rows): void
    {
        usort($rows, static fn (array $a, array $b): int => ($a['id'] ?? 0) <=> ($b['id'] ?? 0));

        $json = json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new \RuntimeException('No se pudo generar el JSON.');
        }

        file_put_contents($this->getDataPath($params, $fileName), $json . PHP_EOL);
    }

    private function getDataPath(ParameterBagInterface $params, string $fileName): string
    {
        return $params->get('kernel.project_dir') . '/data/' . $fileName;
    }

    private function resolveJsonId(array $rows, mixed $rawId): int
    {
        $id = (int) $rawId;
        if ($id > 0) {
            return $id;
        }

        $ids = array_map(static fn (array $row): int => (int) ($row['id'] ?? 0), $rows);

        return $ids ? max($ids) + 1 : 1;
    }

    private function upsertJsonRow(array &$rows, array $row): void
    {
        foreach ($rows as $index => $existing) {
            if ((int) ($existing['id'] ?? 0) === (int) $row['id']) {
                $rows[$index] = $row;
                return;
            }
        }

        $rows[] = $row;
    }
}
