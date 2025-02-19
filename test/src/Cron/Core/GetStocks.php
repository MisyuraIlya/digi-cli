<?php

namespace App\Cron\Core;

use App\Erp\Core\ErpManager;
use App\Repository\ProductRepository;

class GetStocks
{

    public function __construct(
        private readonly ProductRepository $productRepository,
        private readonly ErpManager $erpManager
    )
    {
    }

    public function sync()
    {
        $res = $this->erpManager->GetStocks();
        foreach ($res->stocks as $itemRec){
            $product = $this->productRepository->findOneBySku($itemRec->sku);
            if($product){
                $product->setStock($itemRec->stock);
                $this->productRepository->createProduct($product, true);

            }
        }
    }
}