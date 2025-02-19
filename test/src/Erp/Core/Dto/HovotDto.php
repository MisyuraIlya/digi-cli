<?php

namespace App\Erp\Core\Dto;

class HovotDto
{
    public ?int $total;

    /** @var HovotLineDto[] */
    public array $lines = [];
}