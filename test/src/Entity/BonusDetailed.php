<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\BonusDetailedRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: BonusDetailedRepository::class)]
#[ApiResource]
class BonusDetailed
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'bonusDetaileds')]
    private ?Bonus $bonus = null;

    #[ORM\ManyToOne(inversedBy: 'bonusDetaileds')]
    #[Groups(['bonus:details'])]
    private ?Product $product = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['product:read','bonus:details'])]
    private ?int $minimumQuantity = null;

    #[ORM\ManyToOne(inversedBy: 'bonusProductDetaildes')]
    #[Groups(['product:read', 'bonus:details'])]
    private ?Product $bonusProduct = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['product:read','bonus:details'])]
    private ?int $bonusQuantity = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBonus(): ?Bonus
    {
        return $this->bonus;
    }

    public function setBonus(?Bonus $bonus): static
    {
        $this->bonus = $bonus;

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

    public function getMinimumQuantity(): ?int
    {
        return $this->minimumQuantity;
    }

    public function setMinimumQuantity(?int $minimumQuantity): static
    {
        $this->minimumQuantity = $minimumQuantity;

        return $this;
    }

    public function getBonusProduct(): ?Product
    {
        return $this->bonusProduct;
    }

    public function setBonusProduct(?Product $bonusProduct): static
    {
        $this->bonusProduct = $bonusProduct;

        return $this;
    }

    public function getBonusQuantity(): ?int
    {
        return $this->bonusQuantity;
    }

    public function setBonusQuantity(?int $bonusQuantity): static
    {
        $this->bonusQuantity = $bonusQuantity;

        return $this;
    }
}
