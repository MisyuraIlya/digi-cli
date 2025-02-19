<?php

namespace App\Erp\Core\Dto;

class PriceOnlineDto
{
    public ?string $currency;
    public ?float $basePrice;
    public ?float $priceLvl1;
    public ?float $discountLvl1;
    public ?float $priceLvl2;
    public ?float $discountLvl2;
    public ?float $priceLvl3;
    public ?float $discountLvl3;
    public ?float $priceLvl4;
    public ?float $discountLvl4;
    public ?float $priceLvl5;
    public ?float $discountLvl5;
}