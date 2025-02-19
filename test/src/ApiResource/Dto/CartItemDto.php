<?php

namespace App\ApiResource\Dto;

use App\Entity\Product;

class CartItemDto
{
    public ?string $sku;
    public ?int $quantity;
    public ?float $price;
    public ?int $discount;
    public ?float $total;
    public ?int $stock;

    public ?Product $product;
}