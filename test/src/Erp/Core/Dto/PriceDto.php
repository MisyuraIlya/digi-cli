<?php

namespace App\Erp\Core\Dto;

class PriceDto
{
    public ?string $sku;
    public ?string $basePrice;
    public ?int $price;
    public ?int $discountPrecent;
    public ?\DateTimeImmutable $discountExparationDate;
    public ?int $priceAfterDiscount;
    public ?int $vatPrice;
    public ?int $vatAfterDiscount;

}