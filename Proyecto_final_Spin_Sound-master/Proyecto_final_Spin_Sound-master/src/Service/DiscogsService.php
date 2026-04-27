<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DiscogsService
{
    private const BASE_URL = 'https://api.discogs.com';

    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly string $token,
        private readonly string $projectDir = '',
        private readonly ?LoggerInterface $logger = null,
    ) {}

    /**
     * Busca un release por artista + título y devuelve el primer resultado.
     * Si se pasa un $discogsId busca directamente ese release.
     */
    public function getReleaseData(string $artista, string $titulo, ?int $discogsId = null): ?array
    {
        try {
            if ($discogsId) {
                return $this->fetchRelease($discogsId);
            }

            // 1) Buscar en el catálogo
            $response = $this->client->request('GET', self::BASE_URL . '/database/search', [
                'headers' => $this->headers(),
                'query'   => [
                    'artist' => $artista,
                    'title'  => $titulo,
                    'type'   => 'masters',
                    'per_page' => 1,
                ],
            ]);

            $data = $response->toArray();

            if (empty($data['results'])) {
                $this->logger?->debug('Discogs search returned no results', ['artist' => $artista, 'title' => $titulo]);
                return null;
            }

            $releaseId = $data['results'][0]['id'];
            return $this->fetchRelease($releaseId);

        } catch (\Throwable $e) {
            $this->logger?->error('Error fetching release data from Discogs', ['exception' => $e->getMessage(), 'artist' => $artista, 'title' => $titulo, 'discogsId' => $discogsId]);
            return null;
        }
    }

    /**
     * Obtiene los detalles completos de un release por su ID de Discogs.
     */
    public function fetchRelease(int $id, string $type = 'releases'): ?array
    {
        try {
            $response = $this->client->request('GET', self::BASE_URL . '/' . $type . '/' . $id, [
                'headers' => $this->headers(),
            ]);

            return $response->toArray();
        } catch (\Throwable $e) {
            $this->logger?->error('Error fetching release from Discogs', ['exception' => $e->getMessage(), 'discogsId' => $id]);
            return null;
        }
    }

    /**
     * Extrae la URL de la imagen principal de un release.
     * Prioriza 'primary' image, o la primera disponible.
     */
    public function getImageUrl(?array $releaseData): ?string
    {
        if (!$releaseData || empty($releaseData['images'])) {
            $this->logger?->debug('No images found in release data', ['releaseData_keys' => $releaseData ? array_keys($releaseData) : null]);
            return null;
        }

        // Buscar imagen primaria
        foreach ($releaseData['images'] as $image) {
            if (($image['type'] ?? '') === 'primary' && !empty($image['uri'])) {
                return $image['uri'];
            }
        }

        // Si no hay primaria, devolver la primera imagen
        $uri = $releaseData['images'][0]['uri'] ?? null;
        if ($uri) {
            return $uri;
        }

        $this->logger?->debug('Images array exists but no uri found', ['images' => $releaseData['images']]);
        return null;
    }

    /**
     * Busca lanzamientos por texto libre (para el buscador general).
     * Filtra solo vinilos (Vinyl).
     */
    public function search(string $query, int $perPage = 20, int $page = 1): array
    {
        try {
            $response = $this->client->request('GET', self::BASE_URL . '/database/search', [
                'headers' => $this->headers(),
                'query'   => [
                    'q'        => $query,
                    'type'     => 'release',
                    'format'   => 'vinyl',  // Solo vinilos
                    'per_page' => $perPage,
                    'page'     => $page,
                ],
            ]);

            return $response->toArray();
        } catch (\Throwable $e) {
            $this->logger?->error('Discogs search error', ['exception' => $e->getMessage(), 'query' => $query]);
            return ['results' => [], 'pagination' => []];
        }
    }

    /**
     * Descarga una imagen de Discogs y la guarda en public/img/vinilos/.
     * Si ya existe el archivo local, no la vuelve a descargar.
     * Devuelve la ruta relativa pública (ej: /img/vinilos/discogs_24047.jpeg) o null.
     */
    public function downloadImage(string $url, string $filename): ?string
    {
        $dir = $this->projectDir . '/public/img/vinilos/';

        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $localPath = $dir . $filename;

        // Si ya existe, no descargar de nuevo
        if (file_exists($localPath)) {
            return '/img/vinilos/' . $filename;
        }

        $context = stream_context_create([
            'http' => [
                'header'  => "User-Agent: SpinSoundApp/1.0\r\nAuthorization: Discogs token=" . $this->token . "\r\n",
                'timeout' => 15,
            ],
        ]);

        $imageData = @file_get_contents($url, false, $context);

        if ($imageData === false) {
            $this->logger?->warning('No se pudo descargar la imagen', ['url' => $url]);
            return null;
        }

        file_put_contents($localPath, $imageData);
        $this->logger?->info('Imagen guardada localmente', ['path' => $localPath]);

        return '/img/vinilos/' . $filename;
    }

    public function getProjectDir(): string
    {
        return $this->projectDir;
    }

    private function headers(): array
    {
        $headers = [
            'User-Agent' => 'SpinSoundApp/1.0 +https://spinsound.com',
        ];

        if ($this->token) {
            $headers['Authorization'] = 'Discogs token=' . $this->token;
        } else {
            $this->logger?->warning('Discogs token is empty. API requests may be rate-limited or blocked. Set DISCOGS_TOKEN in .env.');
        }

        return $headers;
    }
}
