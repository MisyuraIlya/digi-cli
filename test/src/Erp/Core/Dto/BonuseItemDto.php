<?php

namespace App\Erp\Core\Dto;

class BonuseItemDto
{
    public ?string $sku;
    public  $minimumQuantity;
    public ?string $bonusSku;
    public  $bonusQuantity;

    public ?string $userExtId;

    public ?string $extId;

    public ?string $title;

    public ?string $fromDate;

    public ?string $expiredAt;

}