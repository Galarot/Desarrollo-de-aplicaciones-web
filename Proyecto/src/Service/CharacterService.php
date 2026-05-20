<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class CharacterService
{
    public function __construct(private ParameterBagInterface $params)
    {
    }

    public function getAllCharacters(): array
    {
        $jsonPath = $this->params->get('kernel.project_dir') . '/data/characters.json';
        if (!file_exists($jsonPath)) {
            return [];
        }
        return json_decode(file_get_contents($jsonPath), true) ?: [];
    }

    public function getAllSplash(): array
    {
        $jsonPath = $this->params->get('kernel.project_dir') . '/data/splash.json';
        if (!file_exists($jsonPath)) {
            return [];
        }
        return json_decode(file_get_contents($jsonPath), true) ?: [];
    }
}
