<?php

namespace App\Erp\Core\Dto;

class AgentStatisticDto
{
//    public float $total;
//    public int $totalOrders;

//    public float $averageTotalBasket;
//    public float $totalPriceToday;
//    public float $totalPriceMonth;
//    public float $totalOrdersToday;
//    public float $totalOrdersMonth;
    public array $monthlyTotals;




    // AVERAGES
    public ?float $averageTotalBasketChoosedDates;
    public ?float $averageTotalBasketMonth;
    public ?float $averageTotalBasketToday;

    // TOTAL
    public ?int $totalInvoicesChoosedDates;
    public ?int $totalInvoicesMonth;
    public ?int $totalInvoicesToday;

    // PRICES
    public ?int $totalPriceChoosedDates;
    public ?float $totalPriceMonth;
    public ?float $totalPriceToday;


}