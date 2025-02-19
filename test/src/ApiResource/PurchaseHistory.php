<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Product;
use App\Entity\User;
use App\State\PurchaseHistoryProvider;
use ApiPlatform\Metadata\Link;


#[ApiResource(
    shortName: 'Documents',
    operations: [
        new GetCollection(
            uriTemplate: '/purchaseHistory/{userExtId}/{sku}',
            provider: PurchaseHistoryProvider::class,
        ),
    ],
)]
class PurchaseHistory
{


    public function __construct()
    {

    }


}