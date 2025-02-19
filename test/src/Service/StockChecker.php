<?php

namespace App\Service;

use ApiPlatform\Doctrine\Orm\Paginator;
use App\Entity\Product;
use App\Erp\Core\ErpManager;

class StockChecker
{
    public function __construct(
        private readonly ErpManager $erpManager
    )
    {
        $this->isOnlineStock = $_ENV['IS_STOCK_ONLINE'] === "true";
    }
    public function StockHandler(Paginator $paginator)
    {
        if( $this->isOnlineStock){
            $this->GetOnlineStock($paginator);
        } else {
        }
    }

    private function GetOnlineStockWithWareHouse(Paginator $paginator)
    {
        foreach ($paginator->getIterator() as $item) {
            assert($item instanceof Product);
            $stock = $this->erpManager->GetStockOnline($item->getSku(),'10');
            if($stock){
                $item->setStock($stock->stock);
            }
        }
    }

    private function GetOnlineStock( Paginator $paginator)
    {
        $skus = [];
        foreach ($paginator->getIterator() as $item) {
            $skus[] = $item->getSku();
        }
        $data = $this->erpManager->GetStocksOnline($skus);
        foreach ($paginator->getIterator() as $item) {
            assert($item instanceof Product);
            foreach ($data->stocks as $stock) {
                if($stock->sku == $item->getSku()){
                    $item->setStock($stock->stock);
                }
            }

        }
    }



}