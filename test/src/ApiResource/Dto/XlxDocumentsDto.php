<?php

namespace App\ApiResource\Dto;

class XlxDocumentsDto
{
    /** @var XlxDto[] */
    public array $documents = [];

    public ?int $totalPrice;

    public ?int $totalPriceBeforeTax;
}