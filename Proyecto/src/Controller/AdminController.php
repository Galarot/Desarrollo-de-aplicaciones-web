<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    // carga la lista de usus y personajes para el panel
    #[Route('/users', name: 'app_admin_users')]
    public function index(EntityManagerInterface $em, ParameterBagInterface $params): Response
    {
        $users = $em->getRepository(User::class)->findAll();

        return $this->render('admin/users.html.twig', [
            'users' => $users,
            'characters' => $this->loadJson($params, 'characters.json'),
            'splashCharacters' => $this->loadJson($params, 'splash.json'),
            'summonBannerUrl' => '/assets/multimedia/bannerprueba.png',
            'summonBannerVersion' => $this->getPublicFileVersion($params, 'assets/multimedia/bannerprueba.png'),
        ]);
    }

    // cambia los roles y el ban de los usus
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
        $roles[] = 'ROLE_USER';
        $roles = array_unique($roles);

        $user->setRoles($roles);
        $user->setBanned($request->request->get('banned') === '1');
        $em->flush();

        $this->addFlash('success', 'Permisos de usuario actualizados.');

        return $this->redirectToRoute('app_admin_users');
    }

    // guarda o actualiza un personaje en el json
    #[Route('/characters/save', name: 'app_admin_character_save', methods: ['POST'])]
    public function saveCharacter(Request $request, ParameterBagInterface $params): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('admin_character_save', (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF no valido.');
        }

        $characters = $this->loadJson($params, 'characters.json');
        $id = $this->resolveJsonId($characters, $request->request->get('id'));
        $existing = $this->findJsonRow($characters, $id);
        $artUrl = (string) ($existing['art_cart_url'] ?? $request->request->get('art_cart_url', ''));
        $uploadedArt = $request->files->get('art_cart_file');

        if ($uploadedArt instanceof UploadedFile) {
            try {
                $artUrl = $this->saveUploadedImage($params, $uploadedArt, 'arts', $id, (string) $request->request->get('nombre'));
            } catch (\RuntimeException $exception) {
                $this->addFlash('error', $exception->getMessage());

                return $this->redirectToRoute('app_admin_users');
            }
        }

        if ($artUrl === '') {
            $this->addFlash('error', 'Debes subir una imagen para el art cart del personaje.');

            return $this->redirectToRoute('app_admin_users');
        }

        $character = [
            'id' => $id,
            'nombre' => trim((string) $request->request->get('nombre')),
            'anio' => (int) $request->request->get('anio'),
            'art_cart_url' => $artUrl,
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

    // guarda o actualiza un splash en el json
    #[Route('/splash/save', name: 'app_admin_splash_save', methods: ['POST'])]
    public function saveSplash(Request $request, ParameterBagInterface $params): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('admin_splash_save', (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF no valido.');
        }

        $splashCharacters = $this->loadJson($params, 'splash.json');
        $id = $this->resolveJsonId($splashCharacters, $request->request->get('id'));
        $existing = $this->findJsonRow($splashCharacters, $id);
        $artUrl = (string) ($existing['art_url'] ?? $request->request->get('art_url', ''));
        $uploadedArt = $request->files->get('splash_file');

        if ($uploadedArt instanceof UploadedFile) {
            try {
                $artUrl = $this->saveUploadedImage($params, $uploadedArt, 'splash', $id, (string) $request->request->get('nombre'));
            } catch (\RuntimeException $exception) {
                $this->addFlash('error', $exception->getMessage());

                return $this->redirectToRoute('app_admin_users');
            }
        }

        if ($artUrl === '') {
            $this->addFlash('error', 'Debes subir una imagen splash para el personaje.');

            return $this->redirectToRoute('app_admin_users');
        }

        $splash = [
            'id' => $id,
            'nombre' => trim((string) $request->request->get('nombre')),
            'art_url' => $artUrl,
        ];

        $this->upsertJsonRow($splashCharacters, $splash);
        $this->saveJson($params, 'splash.json', $splashCharacters);
        $this->addFlash('success', 'Personaje guardado en splash.json.');

        return $this->redirectToRoute('app_admin_users');
    }

    // sube el nuevo banner de invocaciones
    #[Route('/summon/banner/save', name: 'app_admin_summon_banner_save', methods: ['POST'])]
    public function saveSummonBanner(Request $request, ParameterBagInterface $params): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('admin_summon_banner_save', (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF no valido.');
        }

        $banner = $request->files->get('banner_file');
        if (!$banner instanceof UploadedFile) {
            $this->addFlash('error', 'Debes seleccionar una imagen para el banner.');

            return $this->redirectToRoute('app_admin_users');
        }

        try {
            $this->saveBannerImage($params, $banner);
        } catch (\RuntimeException $exception) {
            $this->addFlash('error', $exception->getMessage());

            return $this->redirectToRoute('app_admin_users');
        }

        $this->addFlash('success', 'Banner de invocacion actualizado.');

        return $this->redirectToRoute('app_admin_users');
    }

    // lee los datos de un archivo json
    private function loadJson(ParameterBagInterface $params, string $fileName): array
    {
        $path = $this->getDataPath($params, $fileName);
        if (!is_file($path)) {
            return [];
        }

        $data = json_decode((string) file_get_contents($path), true);

        return is_array($data) ? $data : [];
    }

    // escribe los datos en un archivo json
    private function saveJson(ParameterBagInterface $params, string $fileName, array $rows): void
    {
        usort($rows, static fn (array $a, array $b): int => ($a['id'] ?? 0) <=> ($b['id'] ?? 0));

        $json = json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new \RuntimeException('No se pudo generar el JSON.');
        }

        file_put_contents($this->getDataPath($params, $fileName), $json . PHP_EOL);
    }

    // saca la ruta del archivo de datos
    private function getDataPath(ParameterBagInterface $params, string $fileName): string
    {
        return $params->get('kernel.project_dir') . '/data/' . $fileName;
    }

    // saca la version del archivo para evitar cache
    private function getPublicFileVersion(ParameterBagInterface $params, string $relativePath): int
    {
        $path = $params->get('kernel.project_dir') . '/public/' . $relativePath;

        return is_file($path) ? (int) filemtime($path) : time();
    }

    // busca o genera un id para el json
    private function resolveJsonId(array $rows, mixed $rawId): int
    {
        $id = (int) $rawId;
        if ($id > 0) {
            return $id;
        }

        $ids = array_map(static fn (array $row): int => (int) ($row['id'] ?? 0), $rows);

        return $ids ? max($ids) + 1 : 1;
    }

    // busca una fila por id en el json
    private function findJsonRow(array $rows, int $id): ?array
    {
        foreach ($rows as $row) {
            if ((int) ($row['id'] ?? 0) === $id) {
                return $row;
            }
        }

        return null;
    }

    // inserta o actualiza una fila en el array
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

    // guarda una imagen subida en la carpeta de assets
    private function saveUploadedImage(ParameterBagInterface $params, UploadedFile $file, string $folder, int $id, string $name): string
    {
        $fileName = basename(str_replace('\\', '/', $file->getClientOriginalName()));
        if ($fileName === '') {
            throw new \RuntimeException('El archivo subido no tiene un nombre valido.');
        }

        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION) ?: $file->guessExtension() ?: $file->getClientOriginalExtension() ?: 'png');
        if (!in_array($extension, ['png', 'jpg', 'jpeg', 'webp'], true)) {
            throw new \RuntimeException('Formato de imagen no permitido.');
        }

        $targetDir = $params->get('kernel.project_dir') . '/public/assets/multimedia/' . $folder;

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0775, true);
        }

        $file->move($targetDir, $fileName);

        return '/assets/multimedia/' . $folder . '/' . $fileName;
    }

    // guarda el banner de invocaciones
    private function saveBannerImage(ParameterBagInterface $params, UploadedFile $file): void
    {
        $extension = strtolower($file->guessExtension() ?: $file->getClientOriginalExtension() ?: 'png');
        if (!in_array($extension, ['png', 'jpg', 'jpeg', 'webp'], true)) {
            throw new \RuntimeException('Formato de imagen no permitido.');
        }

        $targetPath = $params->get('kernel.project_dir') . '/public/assets/multimedia/bannerprueba.png';
        $contents = file_get_contents($file->getPathname());
        $image = $contents !== false && function_exists('imagecreatefromstring') && function_exists('imagepng') ? imagecreatefromstring($contents) : false;

        if ($image) {
            imagepng($image, $targetPath);
            imagedestroy($image);
            return;
        }

        $file->move(dirname($targetPath), basename($targetPath));
    }

    // limpia el nombre para la url
    private function slugify(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/i', '-', $value) ?: 'imagen';
        $value = trim($value, '-');

        return $value !== '' ? $value : 'imagen';
    }
}
}
