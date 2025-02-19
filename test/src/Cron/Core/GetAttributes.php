<?php

namespace App\Cron\Core;

use App\Entity\AttributeMain;
use App\Entity\AttributeSub;
use App\Entity\ProductAttribute;
use App\Erp\Core\ErpManager;
use App\Repository\AttributeMainRepository;
use App\Repository\AttributeSubRepository;
use App\Repository\ProductAttributeRepository;
use App\Repository\ProductRepository;

class GetAttributes
{
    public function __construct(
        private readonly AttributeMainRepository $attributeMainRepository,
        private readonly AttributeSubRepository $attributeSubRepository,
        private readonly ProductAttributeRepository $productAttributeRepository,
        private readonly ProductRepository $productRepository,
        private readonly ErpManager $erpManager,
    )
    {
    }

    private function SyncMain()
    {
        $arr = [
            'סוג מוצר',
            'צבע גוף',
            'צבע אור',
            'מתח הזנה',
        ];
        foreach ($arr as $key => $value) {
            $res = $this->attributeMainRepository->findOneBy(['title' => $value]);
            if(!$res){
                $res = new AttributeMain();
                $res->setTitle($value);
                $res->setExtId($key);
                $res->setOrden($key);
                $res->setIsPublished(true);
                $res->setIsInFilter(true);
                $res->setIsInProductCard(true);
                $this->attributeMainRepository->createAttributeMain($res,true);
            }
        }
    }

    private function SyncSubAttributes()
    {

        $products = $this->erpManager->GetProducts(0, 0);
        foreach ($products->products as $product) {
            if ($product->Extra1) {
                $res1 = $this->attributeSubRepository->findOneBy(['title' => $product->Extra1, 'attribute' => 1]);
                if (!$res1) {
                    $res1 = new AttributeSub();
                    $res1->setTitle($product->Extra1);
                    $res1->setAttribute($this->attributeMainRepository->findOneBy(['id' => 1]));
                    $this->attributeSubRepository->createSubAttribute($res1, true);
                }
            }

            if ($product->Extra2) {
                $res2 = $this->attributeSubRepository->findOneBy(['title' => $product->Extra2, 'attribute' => 2]);
                if (!$res2) {
                    $res2 = new AttributeSub();
                    $res2->setTitle($product->Extra2);
                    $res2->setAttribute($this->attributeMainRepository->findOneBy(['id' => 2]));
                    $this->attributeSubRepository->createSubAttribute($res2, true);
                }
            }

            if ($product->Extra3) {
                $res2 = $this->attributeSubRepository->findOneBy(['title' => $product->Extra3, 'attribute' => 3]);
                if (!$res2) {
                    $res2 = new AttributeSub();
                    $res2->setTitle($product->Extra3);
                    $res2->setAttribute($this->attributeMainRepository->findOneBy(['id' => 3]));
                    $this->attributeSubRepository->createSubAttribute($res2, true);
                }
            }

            if ($product->Extra4) {
                $res2 = $this->attributeSubRepository->findOneBy(['title' => $product->Extra4, 'attribute' => 4]);
                if (!$res2) {
                    $res2 = new AttributeSub();
                    $res2->setTitle($product->Extra4);
                    $res2->setAttribute($this->attributeMainRepository->findOneBy(['id' => 4]));
                    $this->attributeSubRepository->createSubAttribute($res2, true);
                }
            }
        }
    }

    private function SyncConnectionProductToSubAttributes()
    {

        $products = $this->erpManager->GetProducts(0, 0);
        foreach ($products->products as $product) {
            $prod = $this->productRepository->findOneBy(['sku' => $product->sku]);
            if($prod){
                if($product->Extra1){
                    $subAt = $this->attributeSubRepository->findOneBy(['title' => $product->Extra1, 'attribute' => 1]);
                    $find = $this->productAttributeRepository->findOneBy(['product' => $prod, 'attributeSub' => $subAt]);
                    if(!$find){
                        $find = new ProductAttribute();
                        $find->setProduct($prod);
                        $find->setAttributeSub($subAt);
                        $this->productAttributeRepository->save($find,true);
                    }
                }

                if( $product->Extra2 ){
                    $subAt2 = $this->attributeSubRepository->findOneBy(['title' => $product->Extra2, 'attribute' => 2]);
                    $find2 = $this->productAttributeRepository->findOneBy(['product' => $prod, 'attributeSub' => $subAt2]);
                    if(!$find2){
                        $find = new ProductAttribute();
                        $find->setProduct($prod);
                        $find->setAttributeSub($subAt2);
                        $this->productAttributeRepository->save($find,true);
                    }
                }

                if( $product->Extra3 ){
                    $subAt2 = $this->attributeSubRepository->findOneBy(['title' => $product->Extra3, 'attribute' => 3]);
                    $find2 = $this->productAttributeRepository->findOneBy(['product' => $prod, 'attributeSub' => $subAt2]);
                    if(!$find2){
                        $find = new ProductAttribute();
                        $find->setProduct($prod);
                        $find->setAttributeSub($subAt2);
                        $this->productAttributeRepository->save($find,true);
                    }
                }

                if( $product->Extra4 ){
                    $subAt2 = $this->attributeSubRepository->findOneBy(['title' => $product->Extra4, 'attribute' => 4]);
                    $find2 = $this->productAttributeRepository->findOneBy(['product' => $prod, 'attributeSub' => $subAt2]);
                    if(!$find2){
                        $find = new ProductAttribute();
                        $find->setProduct($prod);
                        $find->setAttributeSub($subAt2);
                        $this->productAttributeRepository->save($find,true);
                    }
                }

            }
        }

    }

    public function sync()
    {
        $this->syncMain();
        $this->SyncSubAttributes();
        $this->SyncConnectionProductToSubAttributes();
    }
}