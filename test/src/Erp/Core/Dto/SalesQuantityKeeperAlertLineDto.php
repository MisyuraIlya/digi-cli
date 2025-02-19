<?php

namespace App\Erp\Core\Dto;

class SalesQuantityKeeperAlertLineDto
{
    public ?float $sumPreviousMonthCurrentYear;
    public ?float $sumPreviousMonthPreviousYear;
    public ?float $averageLastThreeMonths;
    public ?string $sku;
    public ?string $productDescription;
}