<?php

namespace App\Entity;

use App\Repository\ProductAttributeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ProductAttributeRepository::class)]
class ProductAttribute
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'productAttributes')]
    private ?Product $product = null;

    #[Groups(['product:read','category:read'])]
    #[ORM\ManyToOne(inversedBy: 'productAttributes')]
    private ?AttributeSub $attributeSub = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getAttributeSub(): ?AttributeSub
    {
        return $this->attributeSub;
    }

    public function setAttributeSub(?AttributeSub $attributeSub): static
    {
        $this->attributeSub = $attributeSub;

        return $this;
    }
}
