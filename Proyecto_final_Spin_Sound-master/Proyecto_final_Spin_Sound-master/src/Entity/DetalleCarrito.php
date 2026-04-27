<?php

namespace App\Entity;

use App\Repository\DetalleCarritoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DetalleCarritoRepository::class)]
class DetalleCarrito
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $cantidad = null;

    #[ORM\Column]
    private ?float $precio = null;

    #[ORM\ManyToOne(inversedBy: 'detalleCarritos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Vinilo $vinilo = null;

    #[ORM\ManyToOne(inversedBy: 'detalleCarritos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Carrito $carrito = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCantidad(): ?int
    {
        return $this->cantidad;
    }

    public function setCantidad(int $cantidad): static
    {
        $this->cantidad = $cantidad;

        return $this;
    }

    public function getPrecio(): ?float
    {
        return $this->precio;
    }

    public function setPrecio(float $precio): static
    {
        $this->precio = $precio;

        return $this;
    }

    public function getVinilo(): ?Vinilo
    {
        return $this->vinilo;
    }

    public function setVinilo(?Vinilo $vinilo): static
    {
        $this->vinilo = $vinilo;

        return $this;
    }

    public function getCarrito(): ?Carrito
    {
        return $this->carrito;
    }

    public function setCarrito(?Carrito $carrito): static
    {
        $this->carrito = $carrito;

        return $this;
    }
}
