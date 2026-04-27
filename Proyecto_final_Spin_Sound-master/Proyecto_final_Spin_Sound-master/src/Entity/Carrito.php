<?php

namespace App\Entity;

use App\Repository\CarritoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CarritoRepository::class)]
class Carrito
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var Collection<int, DetalleCarrito>
     */
    #[ORM\OneToMany(targetEntity: DetalleCarrito::class, mappedBy: 'carrito')]
    private Collection $detalleCarritos;

    #[ORM\OneToOne(mappedBy: 'carrito', cascade: ['persist', 'remove'])]
    private ?Cliente $cliente = null;

    public function __construct()
    {
        $this->detalleCarritos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, DetalleCarrito>
     */
    public function getDetalleCarritos(): Collection
    {
        return $this->detalleCarritos;
    }

    public function addDetalleCarrito(DetalleCarrito $detalleCarrito): static
    {
        if (!$this->detalleCarritos->contains($detalleCarrito)) {
            $this->detalleCarritos->add($detalleCarrito);
            $detalleCarrito->setCarrito($this);
        }

        return $this;
    }

    public function removeDetalleCarrito(DetalleCarrito $detalleCarrito): static
    {
        if ($this->detalleCarritos->removeElement($detalleCarrito)) {
            // set the owning side to null (unless already changed)
            if ($detalleCarrito->getCarrito() === $this) {
                $detalleCarrito->setCarrito(null);
            }
        }

        return $this;
    }

    public function getCliente(): ?Cliente
    {
        return $this->cliente;
    }

    public function setCliente(Cliente $cliente): static
    {
        // set the owning side of the relation if necessary
        if ($cliente->getCarrito() !== $this) {
            $cliente->setCarrito($this);
        }

        $this->cliente = $cliente;

        return $this;
    }
}
