<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\HistoryDetailedRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Product;
use App\Entity\History;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: HistoryDetailedRepository::class)]
#[ApiResource(
    normalizationContext: [
        'groups' => ['historyDetailed:read'],
    ],
)]
#[ApiFilter(
    SearchFilter::class,
    properties: [
        'history.id' => 'exact',
    ]
)]
class HistoryDetailed
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['historyDetailed:read','history:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'historyDetaileds')]
    private ?History $history = null;

    #[Groups(['historyDetailed:read','history:read'])]
    #[ORM\ManyToOne(cascade: ['persist', 'remove'])]
    private ?Product $product = null;

    #[Groups(['historyDetailed:read','history:read'])]
    #[ORM\Column(nullable: true)]
    private ?float $singlePrice = null;

    #[Groups(['historyDetailed:read','history:read'])]
    #[ORM\Column(nullable: true)]
    private ?int $quantity = null;

    #[Groups(['historyDetailed:read','history:read'])]
    #[ORM\Column(nullable: true)]
    private ?int $discount = null;

    #[Groups(['historyDetailed:read','history:read'])]
    #[ORM\Column(nullable: true)]
    private ?float $total = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHistory(): ?History
    {
        return $this->history;
    }

    public function setHistory(?History $history): static
    {
        $this->history = $history;

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

    public function getSinglePrice(): ?float
    {
        return $this->singlePrice;
    }

    public function setSinglePrice(?float $singlePrice): static
    {
        $this->singlePrice = $singlePrice;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(?int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getDiscount(): ?int
    {
        return $this->discount;
    }

    public function setDiscount(?int $discount): static
    {
        $this->discount = $discount;

        return $this;
    }

    public function getTotal(): ?float
    {
        return $this->total;
    }

    public function setTotal(?float $total): static
    {
        $this->total = $total;

        return $this;
    }
}
