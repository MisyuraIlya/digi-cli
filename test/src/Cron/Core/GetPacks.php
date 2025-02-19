<?php

namespace App\Cron\Core;

use App\Entity\PackMain;
use App\Erp\Core\ErpManager;
use App\Repository\PackMainRepository;

class GetPacks
{

    public function __construct(
        private readonly PackMainRepository $packMainRepository,
        private readonly ErpManager $erpManager,
    )
    {
    }

    public function sync()
    {
        $response = $this->erpManager->GetPackMain();
        foreach ($response->packs as $itemRec){
            $pack = $this->packMainRepository->findOneByExtIdAndQuantity($itemRec->extId, $itemRec->quantity);
            if(!$pack){
                $pack = new PackMain();
                $pack->setExtId($itemRec->extId);
            }
            $pack->setName($itemRec->name);
            $pack->setQuantity($itemRec->quantity);
            $pack->setBarcode($itemRec->barcode);
            $this->packMainRepository->save($pack, true);
        }
    }
}