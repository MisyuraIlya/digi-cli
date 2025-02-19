<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Product;
use App\State\RestoreCartStateProvider;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    shortName: 'RestoreCart',
    operations: [
        new GetCollection(
            uriTemplate: '/restoreCart/{documentType}/{userId}/{orderNumber}',
            description: 'restore cart by userExtId and sku',
            normalizationContext: [
                'groups' => ['restoreCart:read'],
            ],
            denormalizationContext: [
                'groups' => ['restoreCart:write'],
            ],
            provider: RestoreCartStateProvider::class,
        ),
    ],
)]
class RestoreCart
{
    #[Groups(['restoreCart:read'])]
    public ?string $sku;

    #[Groups(['restoreCart:read'])]
    public ?int $quantity;

    #[Groups(['restoreCart:read'])]
    public ?float $price;

    #[Groups(['restoreCart:read'])]
    public ?int $discount;

    #[Groups(['restoreCart:read'])]
    public ?float $total;

    #[Groups(['restoreCart:read'])]
    public ?int $stock;

    #[Groups(['restoreCart:read'])]
    public ?Product $product;

    /**
     * @return string|null
     */
    public function getSku(): ?string
    {
        return $this->sku;
    }

    /**
     * @param string|null $sku
     */
    public function setSku(?string $sku): void
    {
        $this->sku = $sku;
    }

    /**
     * @return int|null
     */
    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    /**
     * @param int|null $quantity
     */
    public function setQuantity(?int $quantity): void
    {
        $this->quantity = $quantity;
    }

    /**
     * @return float|null
     */
    public function getPrice(): ?float
    {
        return $this->price;
    }

    /**
     * @param float|null $price
     */
    public function setPrice(?float $price): void
    {
        $this->price = $price;
    }

    /**
     * @return int|null
     */
    public function getDiscount(): ?int
    {
        return $this->discount;
    }

    /**
     * @param int|null $discount
     */
    public function setDiscount(?int $discount): void
    {
        $this->discount = $discount;
    }

    /**
     * @return float|null
     */
    public function getTotal(): ?float
    {
        return $this->total;
    }

    /**
     * @param float|null $total
     */
    public function setTotal(?float $total): void
    {
        $this->total = $total;
    }

    /**
     * @return int|null
     */
    public function getStock(): ?int
    {
        return $this->stock;
    }

    /**
     * @param int|null $stock
     */
    public function setStock(?int $stock): void
    {
        $this->stock = $stock;
    }

    /**
     * @return Product|null
     */
    public function getProduct(): ?Product
    {
        return $this->product;
    }

    /**
     * @param Product|null $product
     */
    public function setProduct(?Product $product): void
    {
        $this->product = $product;
    }

}