<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class CharacterService
{
    public function __construct(
        private ParameterBagInterface $params,
        private CacheInterface $cache
    ) {
    }

    // pilla todos los personajes del json con cache
    public function getAllCharacters(): array
    {
        return $this->cache->get('characters_data', function (ItemInterface $item) {
            $item->expiresAfter(3600);
            $jsonPath = $this->params->get('kernel.project_dir') . '/data/characters.json';
            if (!file_exists($jsonPath)) {
                return [];
            }

            return json_decode(file_get_contents($jsonPath), true) ?: [];
        });
    }

    // pilla los splash arts del json con cache
    public function getAllSplash(): array
    {
        return $this->cache->get('splash_data', function (ItemInterface $item) {
            $item->expiresAfter(3600);
            $jsonPath = $this->params->get('kernel.project_dir') . '/data/splash.json';
            if (!file_exists($jsonPath)) {
                return [];
            }

            return json_decode(file_get_contents($jsonPath), true) ?: [];
        });
    }
}
