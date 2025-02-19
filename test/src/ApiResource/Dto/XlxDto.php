<?php

namespace App\ApiResource\Dto;

class XlxDto
{
    public ?string $sku;
    public ?string $barcode;
    public ?string $title;
    public ?int $quantity;
    public ?int $price;
    public ?int $discount;
    public ?int $totalBeforeTax;
    public ?int $totalPrice;
    public ?string $image;

}