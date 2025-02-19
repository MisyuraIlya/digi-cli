<?php

namespace App\Entity;

use App\Repository\ProductPackRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ProductPackRepository::class)]
class ProductPack
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['product:read','category:read','restoreCart:read','history:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'packProducts')]
    #[Groups(['product:read','category:read','restoreCart:read','history:read'])]
    private ?PackMain $pack = null;

    #[ORM\ManyToOne(inversedBy: 'packProducts')]
    private ?Product $product = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPack(): ?PackMain
    {
        return $this->pack;
    }

    public function setPack(?PackMain $pack): static
    {
        $this->pack = $pack;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }
}
