<?php

namespace App\Controller;

use App\Erp\Core\ErpManager;
use PHPUnit\Util\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SalesKeeperController extends AbstractController
{
    public function __construct(
        private readonly ErpManager $erpManager,

    )
    {}

    #[Route('/salesKeeper/{userExtId}', name: 'sales_keeper')]
    public function index($userExtId): Response
    {
        $erp =$this->erpManager->SalesKeeperAlert($userExtId);
        return $this->json($erp);
    }

    #[Route('/salesQuantityKeeperAlert/{userExtId}', name: 'sales_keeper_quantity')]
    public function index2($userExtId): Response
    {
        $erp =$this->erpManager->SalesQuantityKeeperAlert($userExtId);
        return $this->json($erp->lines);
    }

}