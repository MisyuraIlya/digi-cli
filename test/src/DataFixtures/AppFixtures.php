<?php

namespace App\DataFixtures;

use App\Factory\AttributeSubFactory;
use App\Factory\HistoryDetailedFactory;
use App\Factory\HistoryFactory;
use App\Factory\PackMainFactory;
use App\Factory\PriceListUserFactory;
use App\Factory\ProductAttributeFactory;
use App\Factory\ProductPackFactory;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use App\Factory\ProductFactory;
use App\Factory\CategoryFactory;
use App\Factory\PriceListFactory;
use App\Factory\UserFactory;
use App\Factory\ProductImagesFactory;
use App\Factory\PriceListDetailedFactory;
use App\Factory\UserInfoFactory;
use App\Factory\MigvanFactory;
use App\Factory\AttributeMainFactory;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{

    public function __construct( private readonly UserRepository $userRepository)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $lvl1Categories = CategoryFactory::createMany(5, ['lvlNumber' => 1]);
        foreach ($lvl1Categories as $lvl1Category) {
            $lvl2Categories = CategoryFactory::createMany(5, ['lvlNumber' => 2, 'parent' => $lvl1Category]);
            foreach ($lvl2Categories as $lvl2Category) {
                CategoryFactory::createMany(3, ['lvlNumber' => 3, 'parent' => $lvl2Category]);
            }
        }

        ProductFactory::createMany(700);

        $jsonUserData = file_get_contents(__DIR__.'/../Factory/JSONS/User.json');
        $userDataArray = json_decode($jsonUserData, true);
        foreach ($userDataArray as $key => $userData) {
            if($key < 500) {
                $parentUser = $this->userRepository->findParent($userData['ext_id']);
                UserFactory::createOne([
                    'extId' => $userData['ext_id'],
                    'name' => $userData['name'],
                    'phone' => $userData['phone'],
                    'parent' => $parentUser,
                ]);
            }
        }

        PriceListFactory::createMany(10);

        PriceListDetailedFactory::createMany(500, function () {
            return [
                'product' => ProductFactory::random(),
                'priceList' => PriceListFactory::random(),
            ];
        });

        PriceListUserFactory::createMany(500, function() {
            return [
                'priceList' => PriceListFactory::random()
            ];
        });

        PackMainFactory::createMany(10);

        ProductPackFactory::createMany(500, function () {
            return [
                'pack'=> PackMainFactory::random(),
                'product'=> ProductFactory::random(),
            ];
        });

        AttributeMainFactory::createMany(5);

        AttributeSubFactory::createMany(20, function () {
            return [
                'attribute' => AttributeMainFactory::random(),
            ];
        });

        ProductAttributeFactory::createMany(500, function () {
            return [
                'product' => ProductFactory::random(),
                'attributeSub' => AttributeSubFactory::random(),
            ];
        });

        MigvanFactory::createMany(500, function () {
            return [
                'user' => UserFactory::random(),
                'sku' => ProductFactory::random(),
            ];
        });

        HistoryFactory::createMany(100, function () {
            return [
              'user' => UserFactory::random(),
            ];
        });

        HistoryDetailedFactory::createMany(500, function () {
           return [
               'product' => ProductFactory::random(),
               'history' => HistoryFactory::random(),
           ];
        });

    }
}
