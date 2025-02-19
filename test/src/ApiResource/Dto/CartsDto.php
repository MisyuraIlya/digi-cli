<?php

namespace App\ApiResource\Dto;

use App\Entity\Product;

class CartsDto
{
    /** @var CartItemDto[] */
    public array $cart = [];

}