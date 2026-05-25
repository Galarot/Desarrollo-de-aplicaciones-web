<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'user')]
#[UniqueEntity(fields: ['email'], message: 'Ya existe una cuenta con este correo.')]
#[UniqueEntity(fields: ['username'], message: 'Ese nombre de usuario ya está en uso.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private string $email;

    #[ORM\Column(length: 100, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 100)]
    private string $username;

    #[ORM\Column]
    #[Assert\Length(min: 6)]
    private string $password;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $banned = false;

    #[ORM\Column(options: ['default' => 0])]
    private int $crystals = 0;

    #[ORM\Column(type: 'json')]
    private array $ownedCharacters = [];

    public function getId(): ?int { return $this->id; }

    public function getEmail(): string { return $this->email; }
    public function setEmail(string $email): static { $this->email = $email; return $this; }

    public function getUsername(): string { return $this->username; }
    public function setUsername(string $username): static { $this->username = $username; return $this; }

    public function getUserIdentifier(): string { return $this->email; }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }
    public function setRoles(array $roles): static { $this->roles = $roles; return $this; }

    public function isBanned(): bool { return $this->banned; }
    public function setBanned(bool $banned): static { $this->banned = $banned; return $this; }

    public function getCrystals(): int { return $this->crystals; }
    public function setCrystals(int $crystals): static { $this->crystals = max(0, $crystals); return $this; }
    public function addCrystals(int $amount): static { $this->crystals += max(0, $amount); return $this; }
    public function spendCrystals(int $amount): bool
    {
        if ($amount < 0 || $this->crystals < $amount) {
            return false;
        }

        $this->crystals -= $amount;
        return true;
    }

    public function getOwnedCharacters(): array { return $this->ownedCharacters; }
    public function setOwnedCharacters(array $ownedCharacters): static
    {
        $ids = array_map('intval', $ownedCharacters);
        $this->ownedCharacters = array_values(array_unique(array_filter($ids, static fn (int $id): bool => $id > 0)));

        return $this;
    }
    public function addOwnedCharacter(int $characterId): static
    {
        if ($characterId > 0 && !in_array($characterId, $this->ownedCharacters, true)) {
            $this->ownedCharacters[] = $characterId;
            sort($this->ownedCharacters);
        }

        return $this;
    }

    public function getPassword(): string { return $this->password; }
    public function setPassword(string $password): static { $this->password = $password; return $this; }

    public function eraseCredentials(): void {}
}
