<?php

namespace App\Entity;

use App\Repository\ClienteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClienteRepository::class)]
class Cliente extends Usuario
{
    /**
     * @var Collection<int, Pedido>
     */
    #[ORM\OneToMany(targetEntity: Pedido::class, mappedBy: 'cliente', orphanRemoval: true)]
    private Collection $pedidos;

    #[ORM\OneToOne(inversedBy: 'cliente', cascade: ['persist', 'remove'])]
    private ?Carrito $carrito = null;

    public function __construct()
    {
        parent::__construct();
        $this->pedidos = new ArrayCollection();
        $this->carrito = new Carrito();
    }

    /**
     * @return Collection<int, Pedido>
     */
    public function getPedidos(): Collection
    {
        return $this->pedidos;
    }

    public function addPedido(Pedido $pedido): static
    {
        if (!$this->pedidos->contains($pedido)) {
            $this->pedidos->add($pedido);
            $pedido->setCliente($this);
        }

        return $this;
    }

    public function removePedido(Pedido $pedido): static
    {
        if ($this->pedidos->removeElement($pedido)) {
            // set the owning side to null (unless already changed)
            if ($pedido->getCliente() === $this) {
                $pedido->setCliente(null);
            }
        }

        return $this;
    }

    public function getCarrito(): ?Carrito
    {
        return $this->carrito;
    }

    public function setCarrito(Carrito $carrito): static
    {
        $this->carrito = $carrito;

        return $this;
    }
}
