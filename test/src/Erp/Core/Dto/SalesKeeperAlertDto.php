<?php

namespace App\Erp\Core\Dto;

class SalesKeeperAlertDto
{
    public ?float $sumPreviousMonthCurrentYear;
    public ?float $sumPreviousMonthPreviousYear;
    public ?float $averageLastThreeMonths;
}