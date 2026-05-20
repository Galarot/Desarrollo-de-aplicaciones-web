<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'daily_progress')]
class DailyProgress
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', nullable: false)]
    #[Assert\NotNull]
    private ?User $user = null;

    #[ORM\Column(name: 'game_mode', length: 20)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['classic', 'artcart'])]
    private string $gameMode; // 'classic' or 'artcart'

    #[ORM\Column(name: 'seed')]
    #[Assert\NotNull]
    #[Assert\Type(type: 'integer')]
    private int $seed; // YYYYMMDD

    #[ORM\Column(name: 'completed')]
    #[Assert\NotNull]
    private bool $completed = false;

    public function getId(): ?int { return $this->id; }

    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): static { $this->user = $user; return $this; }

    public function getGameMode(): string { return $this->gameMode; }
    public function setGameMode(string $gameMode): static { $this->gameMode = $gameMode; return $this; }

    public function getSeed(): int { return $this->seed; }
    public function setSeed(int $seed): static { $this->seed = $seed; return $this; }

    public function isCompleted(): bool { return $this->completed; }
    public function setCompleted(bool $completed): static { $this->completed = $completed; return $this; }
}
