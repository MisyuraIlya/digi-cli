<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Erp\Core\ErpManager;

class PurchaseHistoryProvider implements ProviderInterface
{
    public function __construct(
        private readonly ErpManager $erpManager,
    )
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $userExtId = $uriVariables['userExtId'];
        $sku = $uriVariables['sku'];
        $ErpManager = $this->erpManager->PurchaseHistoryByUserAndSku($userExtId,$sku)->items;
        return $ErpManager;
    }
}
