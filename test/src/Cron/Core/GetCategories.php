<?php

namespace App\Cron\Core;

use App\Entity\Category;
use App\Enum\CategoryEnum;
use App\Erp\Core\Dto\ProductDto;
use App\Erp\Core\ErpManager;
use App\Repository\CategoryRepository;
use Ramsey\Uuid\Guid\Guid;

class GetCategories
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository,
        private readonly ErpManager $erpManager,
    )
    {
    }

    public function CustomLvl1()
    {
        $data = [
            [
                'title' => 'תאורת פנים',
                'groups' => [122,107,109,103,106],
            ],
            [
                'title' => 'תאורה חוץ',
                'groups' => [104,135],
            ],
            [
                'title' => 'מאווררי תקרה',
                'groups' => []
            ]
        ];

        foreach ($data as $key => $categoryData) {
            $category = $this->categoryRepository->findOneBy(['title' => $categoryData['title']]);
            if (!$category) {
                $category = new Category();
                $category->setTitle($categoryData['title']);
                $category->setIsPublished(true);
                $category->setLvlNumber(1);
                $category->setIsPublished(true);
                $uuid = Guid::uuid4()->toString();
                $category->setExtId($uuid);
            }
            $category->setIntegrationGroups($categoryData['groups']);
            $this->categoryRepository->createCategory($category, true);
        }
    }

    private function SyncLvl2()
    {
        $data = $this->erpManager->getCategories();
        foreach ($data->categories as $itemRec) {
            $category = $this->categoryRepository->findOneBy(['extId' => $itemRec->categoryId, 'lvlNumber' => 2]);
            if(!$category) {
                $category = new Category();
                $category->setExtId($itemRec->categoryId);
                $category->setLvlNumber(2);
                $category->setIsPublished(true);
            }
            $category->setTitle($itemRec->categoryName);
            foreach ($this->categoryRepository->findBy(['lvlNumber' => 1]) as $existingCategory) {
                if (in_array($itemRec->categoryId, $existingCategory->getIntegrationGroups())) {
                    $category->setParent($existingCategory);
                    break;
                }
            }
            $this->categoryRepository->createCategory($category, true);
        }
    }

    private function SyncLvl3()
    {
        $data = $this->erpManager->getCategories();
        foreach ($data->categories as $itemRec) {
            $parent = $this->categoryRepository->findOneBy(['extId' => $itemRec->categoryId, 'lvlNumber' => 2]);
            if($parent){
                $parentId = $parent->getId();
                $category = $this->categoryRepository->findOneBy(['extId' => $itemRec->parentId, 'lvlNumber' => 3, 'parent' => $parentId ]);
                if(!$category) {
                    $category = new Category();
                    $category->setExtId($itemRec->parentId);
                    $category->setLvlNumber(3);
                    $category->setIsPublished(true);
                    $category->setParent($parent);
                }
                $category->setTitle($itemRec->parentName);
                $this->categoryRepository->createCategory($category, true);
            }


        }
    }

    public function sync()
    {
        $this->customLvl1();
        $this->syncLvl2();
        $this->syncLvl3();
    }

}