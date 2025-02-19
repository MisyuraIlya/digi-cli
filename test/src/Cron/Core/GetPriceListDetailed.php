<?php

namespace App\Cron\Core;

use App\Entity\PriceListDetailed;
use App\Erp\Core\ErpManager;
use App\Repository\PriceListDetailedRepository;
use App\Repository\PriceListRepository;
use App\Repository\ProductRepository;

class GetPriceListDetailed
{
    public function __construct(
        private readonly ProductRepository $productRepository,
        private readonly PriceListRepository $priceListRepository,
        private readonly PriceListDetailedRepository $priceListDetailedRepository,
        private readonly ErpManager $erpManager,
    )
    {
    }

    public function sync()
    {
        $response = $this->erpManager->GetPriceListDetailed();
        foreach ($response->priceListsDetailed as $itemRec){
            $findPriceList = $this->priceListRepository->findOneByExtId($itemRec->priceList);
            $findProduct = $this->productRepository->findOneBySku($itemRec->sku);
            $priceListDetailed = $this->priceListDetailedRepository->findOneBySkuAndPriceList($itemRec->sku, $itemRec->priceList);
            if(!$priceListDetailed) {
                $priceListDetailed = new PriceListDetailed();
                $priceListDetailed->setProduct($findProduct);
                $priceListDetailed->setPriceList($findPriceList);
            }
            $priceListDetailed->setPrice($itemRec->price);
            $priceListDetailed->setDiscount($itemRec->discount);
            $this->priceListDetailedRepository->createPriceListDetailed($priceListDetailed,true);
        }
    }
}