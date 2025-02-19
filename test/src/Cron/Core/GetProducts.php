<?php

namespace App\Cron\Core;

use App\Entity\Product;
use App\Erp\Core\Dto\ProductDto;
use App\Erp\Core\ErpManager;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;

class GetProducts
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository,
        private readonly ProductRepository $productRepository,
        private readonly ErpManager $erpManager,
    )
    {
    }

    public function sync()
    {
        $products = $this->erpManager->getProducts(0,0);
        foreach ($products->products as $product) {
            assert($product instanceof ProductDto);
            $entity = $this->productRepository->findOneBy(['sku' => $product->sku]);
            if (!$entity) {
                $entity = new Product();
                $entity->setSku($product->sku);
                $entity->setIsPublished(true);
                $entity->setCreatedAt(new \DateTimeImmutable());
                $entity->setIsNew(false);
                $entity->setIsSpecial(false);
            }
            $entity->setTitle($product->title);
            $entity->setBarcode($product->barcode);
            if($product->categoryLvl2Id){
                $catlvl2 = $this->categoryRepository->findOneBy(['extId'=>$product->categoryLvl2Id]);
                if($catlvl2){
                    $entity->setCategoryLvl2($catlvl2);
                    $entity->setCategoryLvl1($catlvl2->getParent());
                }
            }
            if($product->categoryLvl3Id){
                $catlvl2 = $this->categoryRepository->findOneBy(['extId'=>$product->categoryLvl2Id]);

                if($catlvl2){
                    $parentId = $catlvl2->getId();
                    $catlvl3 = $this->categoryRepository->findOneBy(['extId'=>$product->categoryLvl3Id, 'parent'=>$parentId]);
                    if($catlvl3){
                        $entity->setCategoryLvl3($catlvl3);
                    }
                }

            }
            $entity->setUpdatedAt(new \DateTimeImmutable());
            $this->productRepository->createProduct($entity,true);

        }
    }

}