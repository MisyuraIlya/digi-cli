<?php

namespace App\Service;

use ApiPlatform\Doctrine\Orm\Paginator;
use App\ApiResource\Dto\CartsDto;
use App\Entity\HistoryDetailed;
use App\Entity\PriceListUser;
use App\Entity\Product;
use App\Entity\User;
use App\Erp\Core\Dto\PriceDto;
use App\Erp\Core\Dto\PricesDto;
use App\Erp\Core\ErpManager;

class PriceHandler
{
    public function __construct(
        private readonly ErpManager $erpManager
    )
    {
        $this->isOnlinePrice =  $_ENV['IS_ONLINE_PRICE'] === 'true';

    }

    public function HandlePrice(?User $user, Paginator $paginator)
    {
        if($this->isOnlinePrice){
            $this->GetOnlinePirce($user,$paginator);
        } else {
            $this->GetDbPrices($user,$paginator);
        }
    }

    /**
     * @param User|null $user
     * @param array $products Entity Product
     * @return void
     */
    public function HandlePriceArray(?User $user, array $products)
    {
        if($this->isOnlinePrice){
            $this->GetOnlinePriceArray($user,$products);
        } else {
        }
    }

    private function GetOnlinePirce(?User $user, Paginator $paginator)
    {
        if ($user) {
            $priceListUser = $user->getPriceListUsers();
            if (!empty($priceListUser) && isset($priceListUser[0])) {
                $priceList = $priceListUser[0]->getPriceList()->getExtId();

                if (isset($priceList)) {
                    foreach ($paginator->getIterator() as $item) {
                        assert($item instanceof Product);
                        $price = $this->erpManager->GetPriceOnline($user->getExtId(), $item->getSku(), $priceList);
                        if (isset($price->basePrice)) {
                            $item->setFinalPrice($price->basePrice);
                            if (isset($price->priceLvl1)) {
                                $item->setFinalPrice($price->priceLvl1);
                            }
                            if (isset($price->discountLvl1)) {
                                $item->setDiscount($price->discountLvl1);
                            }
                            if (isset($price->priceLvl2)) {
                                $item->setFinalPrice($price->priceLvl2);
                            }
                            if (isset($price->discountLvl2)) {
                                $item->setDiscount($price->discountLvl2);
                            }
                            if (isset($price->priceLvl3)) {
                                $item->setFinalPrice($price->priceLvl3);
                            }
                            if (isset($price->discountLvl3)) {
                                $item->setDiscount($price->discountLvl3);
                            }
                            if (isset($price->priceLvl4)) {
                                $item->setFinalPrice($price->priceLvl4);
                            }
                            if (isset($price->discountLvl4)) {
                                $item->setDiscount($price->discountLvl4);
                            }
                            if (isset($price->priceLvl5)) {
                                $item->setFinalPrice($price->priceLvl5);
                            }
                            if (isset($price->discountLvl5)) {
                                $item->setDiscount($price->discountLvl5);
                            }
                        }
                    }
                }
            } else {

            }
        }
    }

    /**
     * @param User|null $user
     * @param array Product Entity $skus
     * @return void
     */
    private function GetOnlinePriceArray(?User $user, array $products)
    {
        if ($user) {
            $priceListUser = $user->getPriceListUsers();
            if (!empty($priceListUser) && isset($priceListUser[0])) {
                $priceList = $priceListUser[0]->getPriceList()->getExtId();
                if (isset($priceList)) {
                    foreach ($products as $item) {
                        assert($item instanceof Product);
                        $price = $this->erpManager->GetPriceOnline($user->getExtId(), $item->getSku(), $priceList);
                        if (isset($price->basePrice)) {
                            $item->setFinalPrice($price->basePrice);
                            if (isset($price->priceLvl1)) {
                                $item->setFinalPrice($price->priceLvl1);
                            }
                            if (isset($price->discountLvl1)) {
                                $item->setDiscount($price->discountLvl1);
                            }
                            if (isset($price->priceLvl2)) {
                                $item->setFinalPrice($price->priceLvl2);
                            }
                            if (isset($price->discountLvl2)) {
                                $item->setDiscount($price->discountLvl2);
                            }
                            if (isset($price->priceLvl3)) {
                                $item->setFinalPrice($price->priceLvl3);
                            }
                            if (isset($price->discountLvl3)) {
                                $item->setDiscount($price->discountLvl3);
                            }
                            if (isset($price->priceLvl4)) {
                                $item->setFinalPrice($price->priceLvl4);
                            }
                            if (isset($price->discountLvl4)) {
                                $item->setDiscount($price->discountLvl4);
                            }
                            if (isset($price->priceLvl5)) {
                                $item->setFinalPrice($price->priceLvl5);
                            }
                            if (isset($price->discountLvl5)) {
                                $item->setDiscount($price->discountLvl5);
                            }
                        }
                    }
                }
            } else {

            }
        }
    }

    private function GetDbPrices(?User $user, Paginator $paginator)
    {
        if($user){
            $priceLists = $user->getPriceListUsers();
//            TODO
        }
    }


    public function GetOnlinePriceFromCart(?User $user, CartsDto $cartsDto)
    {
        if($user){
            $skus = [];
            $priceLists = [];
            foreach ($cartsDto->cart as $itmeRec){
                $skus[] = $itmeRec->sku;
            }
            foreach ($user->getPriceListUsers() as $item) {
                $priceLists[] = $item->getPriceList()->getExtId();
            }
            if(!empty($skus)){
                $response = $this->GetPricesBySkusFromPriceList($skus, $priceLists);
                foreach ($response as $item) {
                    foreach ($item['PARTPRICE2_SUBFORM'] as $price) {
                        foreach ($cartsDto->cart as &$product) {
                            if($product->sku == $price['PARTNAME']){
                                $product->product->setFinalPrice($price['PRICE']);
                                $product->price= $product->quantity * $price['PRICE'];
                                $product->total = $product->price;
                            }
                        }
                    }
                }
            }

        }
    }


}