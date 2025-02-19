<?php

namespace App\Controller;


use App\Entity\Notification;
use App\Entity\NotificationUser;
use App\Entity\User;
use App\Enum\PurchaseStatus;
use App\Erp\Core\ErpManager;
use App\helpers\ApiResponse;
use App\Repository\HistoryDetailedRepository;
use App\Repository\HistoryRepository;
use App\Repository\NotificationRepository;
use App\Repository\NotificationUserRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use App\Service\OneSignal;
use PHPUnit\Util\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApproveController extends AbstractController
{

    public function __construct(
        private HistoryRepository $historyRepository,
        private UserRepository $userRepository,
        private readonly ErpManager $erpManager,
        private readonly OneSignal $oneSignal,
        private readonly NotificationRepository $notificationRepository,
        private readonly NotificationUserRepository $notificationUserRepository,
    )
    {
    }

    #[Route('/approve', name: 'approve_order', methods: ['POST'])]
    public function index(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);
            $agentApproved = $data['agentId'];
            $orderId = $data['orderId'];

            $findAgent = $this->userRepository->findOneById($agentApproved);
            if(!$findAgent){
                throw new Exception('לא נמצא סוכן');
            }

            $history = $this->historyRepository->findOneById($orderId);
            if($history){
                $history->setAgentApproved($findAgent);
                $history->setAgent($findAgent);
                $history->setOrderStatus(PurchaseStatus::PENDING);
                $history->setDocumentType('orders');
                $this->historyRepository->save($history,true);
            }


            //            $orderNumber = $this->erpManager->SendOrder($history);
            $orderNumber = '123';
            if($orderNumber){
                $history->setOrderExtId($orderNumber);
                $history->setOrderStatus(PurchaseStatus::PAID);
                $this->historyRepository->save($history,true);
                $userName = $history->getUser()->getExtId() . ' ' . $history->getUser()->getName();
                try {
                    $this->oneSignal
                        ->SendOrderPush($history->getUser(), 'התקבלה הזמנה במערכת', "מספר הזמנה: $orderNumber")
                        ->AlertToAgentsGetOrder('בוצעה הזמנה במערכת',"לקורח $userName ביצעה הזמנה $orderNumber");
                    $this->CreateNotification($history->getUser(), $orderNumber);
                } catch (\Exception $exception){
                }
                $obj = new \stdClass();
                $obj->orderNumber = $orderNumber;
                return  $this->json((new ApiResponse($obj,''))->OnSuccess());
            } else {
                throw new \Exception('הזמנה לא שודרה: לא התקבל מספר הזמנה');
            }
        } catch (\Exception $e) {
            return $this->json((new ApiResponse(null,$e->getMessage()))->OnError());
        }
    }

    private function CreateNotification(User $user, $orderNumber)
    {
        $description = "התקבלה הזמנה מספר" . ' ' .  "$orderNumber";
        $title = "התקבלה הזמנה";
        $createNot = new Notification();
        $createNot->setCreatedAt(new \DateTimeImmutable());
        $createNot->setTitle($title);
        $createNot->setDescription($description);
        $createNot->setIsSend(true);
        $createNot->setIsPublic(false);
        $createNot->setIsPublished(true);
        $createNot->setIsSystem(true);
        $this->notificationRepository->save($createNot,true);

        $userNot = new NotificationUser();
        $userNot->setUser($user);
        $userNot->setNotification($createNot);
        $userNot->setIsRead(false);
        $userNot->setCreatedAt(new \DateTimeImmutable());
        $this->notificationUserRepository->save($userNot,true);

    }




}
