<?php

namespace App\Entity;

use App\Repository\ArtistaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArtistaRepository::class)]
class Artista
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\Column(length: 255)]
    private ?string $nacionalidad = null;

    /**
     * @var Collection<int, Vinilo>
     */
    #[ORM\ManyToMany(targetEntity: Vinilo::class, inversedBy: 'artistas')]
    private Collection $vinilos;

    public function __construct()
    {
        $this->vinilos = new ArrayCollection();
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

    public function getNacionalidad(): ?string
    {
        return $this->nacionalidad;
    }

    public function setNacionalidad(string $nacionalidad): static
    {
        $this->nacionalidad = $nacionalidad;

        return $this;
    }

    /**
     * @return Collection<int, Vinilo>
     */
    public function getVinilos(): Collection
    {
        return $this->vinilos;
    }

    public function addVinilo(Vinilo $vinilo): static
    {
        if (!$this->vinilos->contains($vinilo)) {
            $this->vinilos->add($vinilo);
            $vinilo->addArtista($this);
        }

        return $this;
    }

    public function removeVinilo(Vinilo $vinilo): static
    {
        if ($this->vinilos->removeElement($vinilo)) {
            $vinilo->removeArtista($this);
        }

        return $this;
    }
}
