<?php

namespace App\Erp\Core\Dto;

class DocumentItemDto
{
    public ?string $sku;
    public ?string $title;
    public ?int $quantity;
    public ?float $priceByOne;
    public ?float $total;
    public ?int $discount;

    public array $product;
}