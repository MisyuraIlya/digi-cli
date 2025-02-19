<?php

namespace App\Controller;

use App\Entity\User;
use App\Erp\Core\Dto\AgentStatisticDto;
use App\Erp\Core\ErpManager;
use App\helpers\ApiResponse;
use App\Repository\AgentTargetRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AgentProfileController extends AbstractController
{
    public function __construct(
        private readonly ErpManager $erpManager,
        private readonly UserRepository $userRepository,
        private readonly AgentTargetRepository $agentTargetRepository,
    )
    {}

    #[Route('/agentProfile/{agentId}/{dateFrom}/{dateTo}', name: 'app_agent_profile')]
    public function index($agentId,$dateFrom,$dateTo): Response
    {

        $user = $this->userRepository->findOneById($agentId);
        if(!$user) new BadRequestException('not found user with this id');
        $response = $this->erpManager->GetAgentStatistic($user->getExtId(),$dateFrom,$dateTo);
        $coundUsers = $user->getUsersAgent()->count();
        $res = [
            "agentName" => $user->getName(),
            "agentExtId" => $user->getExtId(),
            "totalPriceMonth" => $response->totalPriceMonth,
            "total" => $response->totalPriceChoosedDates,
            "totalOrders" => $response->totalInvoicesChoosedDates,
            "averageBasket"  => $response->averageTotalBasketChoosedDates,
            "totalClients" => $coundUsers,
            "monthlyTotals" => $response->monthlyTotals,

            "totalPriceDay" => $response->totalPriceToday,
            "totalDayCount" => $response->totalPriceToday,
            "totalMonthCount" => $response->totalPriceMonth,

            "totalMissions" => 0,
            "targetPrecent" => 0,
        ];

        return $this->json($res);

    }

    #[Route('/agentsStatistic/{dateFrom}/{dateTo}', name: 'app_agent_profile_statistic')]
    public function agentsStatistic($dateFrom,$dateTo): Response
    {

        $result = [];

        $users = $this->userRepository->GetAllAgents();
        $total = 0;
        $totalOrders = 0;
        foreach ($users as $userRec){
            assert($userRec instanceof User);
            $response = $this->erpManager->GetAgentStatistic($userRec->getExtId(),$dateFrom,$dateTo);
            assert($response instanceof  AgentStatisticDto);
            $total += $response->totalPriceChoosedDates;
            $totalOrders += $response->totalInvoicesChoosedDates;
            $coundUsers = $userRec->getUsersAgent()->count();
            foreach ($response->monthlyTotals as &$itemRec){
                if(empty(($userRec->getAgentTargets()->toArray()))){
                    $itemRec['target'] = 0;
                    $itemRec['succeed'] = null;
                } else {
                    foreach ($userRec->getAgentTargets() as $subRec){
                        if($itemRec['monthTitle'] == $subRec->getMonth()){
                            $itemRec['target'] = $subRec->getTargetValue();
                            if($subRec->getTargetValue() <= $itemRec['total']){
                                $itemRec['succeed'] = true;
                            } else {
                                $itemRec['succeed'] = false;
                            }
                        } else {
                            $itemRec['target'] = 0;
                            $itemRec['succeed'] = null;
                        }
                    }
                }

            }
            $res = [
                "agentName" => $userRec->getName(),
                "agentExtId" => $userRec->getExtId(),
                "totalPriceMonth" => $response->totalPriceMonth,
                "total" => $response->totalPriceChoosedDates,
                "totalOrders" => $response->totalInvoicesChoosedDates,
                "averageBasket"  => $response->averageTotalBasketChoosedDates,
                "totalClients" => $coundUsers,
                "monthlyTotals" => $response->monthlyTotals,

                "totalPriceDay" => $response->totalPriceToday,
                "totalDayCount" => $response->totalPriceToday,
                "totalMonthCount" => $response->totalPriceMonth,

                "totalMissions" => 0,
                "targetPrecent" => 0,
            ];
            $result[] = $res;
        }

        $obj = new \stdClass();
        $obj->lines = $result;
        $obj->total = $total;
        $obj->totalOrders = $totalOrders;
        if( $obj->totalOrders !== 0) {
            $obj->averageTotal = $obj->total / $obj->totalOrders;
        } else {
            $obj->averageTotal = 0;
        }
        return $this->json($obj);

    }
}
