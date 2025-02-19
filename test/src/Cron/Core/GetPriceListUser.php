<?php

namespace App\Cron\Core;

use App\Entity\PriceListUser;
use App\Entity\User;
use App\Erp\Core\ErpManager;
use App\Repository\PriceListRepository;
use App\Repository\PriceListUserRepository;
use App\Repository\UserRepository;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GetPriceListUser
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly PriceListRepository $priceListRepository,
        private readonly PriceListUserRepository $priceListUserRepository,
        private readonly ErpManager $erpManager,
    )
    {
    }

    public function sync()
    {
        $response = $this->erpManager->GetPriceListUser();
        foreach ($response->priceLists as $itemRec){
            $user = $this->userRepository->findAllExtIdsUsers($itemRec->userExId);
            if($user){
                foreach ($user as $userRec) {
                    assert($userRec instanceof User);
                    $priceList = $this->priceListRepository->findOneByExtId($itemRec->priceListExId);
                    if(!empty($priceList)){
                        $priceListUser = $this->priceListUserRepository->findOneByUserIdAndPriceListId($userRec->getId(),$priceList->getId());
                        if(empty($priceListUser)){
                            $priceListUser = new PriceListUser();
                            $priceListUser->setUser($userRec);
                            $priceListUser->setPriceList($priceList);
                            $this->priceListUserRepository->save($priceListUser,true);
                        }
                    }
                }
            }

        }
    }
}