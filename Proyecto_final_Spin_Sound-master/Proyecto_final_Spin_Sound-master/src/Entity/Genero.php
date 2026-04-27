<?php

namespace App\Entity;

use App\Repository\GeneroRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GeneroRepository::class)]
class Genero
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    /**
     * @var Collection<int, Vinilo>
     */
    #[ORM\ManyToMany(targetEntity: Vinilo::class, inversedBy: 'generos')]
    private Collection $genero_vinilo;

    public function __construct()
    {
        $this->genero_vinilo = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * @return Collection<int, Vinilo>
     */
    public function getGeneroVinilo(): Collection
    {
        return $this->genero_vinilo;
    }

    public function addGeneroVinilo(Vinilo $generoVinilo): static
    {
        if (!$this->genero_vinilo->contains($generoVinilo)) {
            $this->genero_vinilo->add($generoVinilo);
        }

        return $this;
    }

    public function removeGeneroVinilo(Vinilo $generoVinilo): static
    {
        $this->genero_vinilo->removeElement($generoVinilo);

        return $this;
    }
}
