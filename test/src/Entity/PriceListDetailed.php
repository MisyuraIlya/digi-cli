<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\PriceListDetailedRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\PriceList;
use App\Entity\Product;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\Ignore;

#[ORM\Entity(repositoryClass: PriceListDetailedRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Put(),
        new Patch(),
    ],
    normalizationContext: [
        'groups' => ['product:read'],
    ],
    denormalizationContext: [
        'groups' => ['product:write'],
    ],
)]
class PriceListDetailed
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'priceListDetaileds')]
    private ?Product $product = null;

    #[ORM\Column]
    #[Groups(['product:read'])]
    private ?int $price = null;

    #[Groups(['product:read'])]
    #[ORM\Column(nullable: true)]
    private ?int $discount = null;

    #[Groups(['product:read'])]
    #[ORM\ManyToOne(inversedBy: 'priceListDetaileds')]
    private ?PriceList $priceList = null;

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

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): static
    {
        $this->price = $price;

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

    public function getPriceList(): ?PriceList
    {
        return $this->priceList;
    }

    #[Groups(['product:read'])]
    public function getPriceListExId(): ?string
    {
        return $this->priceList->getExtId();
    }

    public function setPriceList(?PriceList $priceList): static
    {
        $this->priceList = $priceList;

        return $this;
    }
}
