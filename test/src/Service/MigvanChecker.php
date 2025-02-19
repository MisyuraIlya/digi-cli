<?php

namespace App\Service;

use App\Entity\User;
use App\Erp\Core\ErpManager;

class MigvanChecker
{
    public function __construct(
        private readonly ErpManager $erpManager
    )
    {
        $this->isOnlinePrice = $_ENV['IS_WITH_MIGVAN'] === "true";
    }
    public function GetMigvanOnline(?User $user): array
    {
        if($this->isOnlinePrice && $user){
            $userExtId = $user->getExtId();
            $endpoint2 = "/CUSTOMERS";
            $queryParameters2 = [
                '$filter' => "CUSTNAME eq '$userExtId'",
                '$select' => 'CUSTNAME',
                '$expand' => 'CUSTPARTPRICE_SUBFORM($select=PARTNAME)',
            ];
            $queryString2 = http_build_query($queryParameters2);
            $urlQuery2 = $endpoint2 . '?' . $queryString2;
            $response = $this->erpManager->GetRequest($urlQuery2);
            $skus = [];
            foreach ($response as $itemRec){
                foreach ($itemRec['CUSTPARTPRICE_SUBFORM'] as $subRec){
                    $skus[] = $subRec['PARTNAME'];
                }
            }
            return $skus;
        } else {
            return [];
        }

    }
}