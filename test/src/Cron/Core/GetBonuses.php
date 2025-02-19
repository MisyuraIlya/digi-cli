<?php

namespace App\Cron\Core;

use App\Entity\Bonus;
use App\Entity\BonusDetailed;
use App\Erp\Core\ErpManager;
use App\Repository\BonusDetailedRepository;
use App\Repository\BonusRepository;
use App\Repository\ProductRepository;

class GetBonuses
{
    public function __construct(
        private readonly ErpManager $erpManager,
        private readonly BonusRepository $bonusRepository,
        private readonly BonusDetailedRepository $bonusDetailedRepository,
        private readonly ProductRepository $productRepository,
    )
    {
    }

    public function sync()
    {
        $response = $this->erpManager->GetBonuses();
//        $response = (object)[
//            "bonuses" => [
//                (object)[
//                    "sku" => "9100206",
//                    "minimumQuantity" => 10,
//                    "bonusSku" => "9100207",
//                    "bonusQuantity" => 1,
//                    "userExtId" => "100045",
//                    "extId" => "100",
//                    "title" => "test",
//                    "fromDate" => "2020-01-01",
//                    "expiredAt" => "2020-01-01",
//                ]
//            ],
//        ];

        foreach ($response->bonuses as $bonus) {
            $bonusEntity = $this->bonusRepository->findOneBy(['userExtId' => $bonus->userExtId, 'extId' => $bonus->extId]);

            if (!$bonusEntity) {
                $bonusEntity = new Bonus();
                $bonusEntity->setExtId($bonus->extId);
                $bonusEntity->setUserExtId($bonus->userExtId);
            }

            $bonusEntity->setExpiredAt(new \DateTimeImmutable($bonus->expiredAt));
            $bonusEntity->setCreatedAt(new \DateTimeImmutable($bonus->fromDate));
            $bonusEntity->setTitle($bonus->title);
            $this->bonusRepository->save($bonusEntity, true);

            $product = $this->productRepository->findOneBy(['sku' => $bonus->sku]);
            $productBonus = $this->productRepository->findOneBy(['sku' => $bonus->bonusSku]);

            if ($productBonus && $product) {
                $detailes = $this->bonusDetailedRepository->findOneByIds(
                    $bonusEntity->getId(),
                    $product->getId(),
                    $productBonus->getId()
                );

                if (!$detailes) {
                    $detailes = new BonusDetailed();
                    $detailes->setBonus($bonusEntity);
                    $detailes->setProduct($product);
                    $detailes->setBonusProduct($productBonus);
                }

                $detailes->setMinimumQuantity((int)$bonus->minimumQuantity);
                $detailes->setBonusQuantity((int)$bonus->bonusQuantity);

                $this->bonusDetailedRepository->save($detailes, true);
            }
        }
    }
}