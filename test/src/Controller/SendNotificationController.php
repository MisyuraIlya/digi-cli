<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Entity\NotificationUser;
use App\Entity\User;
use App\Enum\UsersTypes;
use App\Erp\Core\ErpManager;
use App\helpers\ApiResponse;
use App\Repository\HistoryDetailedRepository;
use App\Repository\HistoryRepository;
use App\Repository\NotificationRepository;
use App\Repository\NotificationUserRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use App\Service\OneSignal;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use function PHPUnit\Framework\throwException;

class SendNotificationController extends AbstractController
{

    public function __construct(
        private readonly NotificationRepository $notificationRepository,
        private readonly OneSignal $oneSignal,
        private readonly UserRepository $userRepository,
        private readonly NotificationUserRepository $notificationUserRepository
    )
    {

    }

    #[Route('/sendNotification', name: 'send_notification', methods: ['POST'])]
    public function index(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);
            $idNotification =  $data['id'];
            $who =  $data['who'];

            $notifc = $this->notificationRepository->findOneById($idNotification);
            if(!$notifc) throw new \Exception('לא נמצא הועדעה כזאת');
            if($who == 'allUsers'){
                $response = $this->oneSignal->SendPushAllUser($notifc->getTitle(),$notifc->getDescription(),'');
                $this->SendNotificationRegisteredUsers($notifc);
            }
            if($who == 'allAgents'){
                $response = $this->oneSignal->SendAllAgents($notifc->getTitle(),$notifc->getDescription(),'');
                $this->SendNotificationRegisteredUsers($notifc, true);
            }
            if(isset($response['id'])){
                $notifc->setIsSend(true);
                $notifc->setIsPublished(true);
                $this->notificationRepository->save($notifc,true);
                return  $this->json((new ApiResponse($response['id'],'הודעה נשלחה בהצלחה!'))->OnSuccess());
            } else {
                throw new \Exception('לא נשלחה הודעה אנא פנה לתמיכה טכנית');
            }
        } catch (\Exception $e) {

            return $this->json((new ApiResponse(null,$e->getMessage()))->OnError());
        }
    }

    private function SendNotificationRegisteredUsers(Notification $notification, bool $isOnlyAgent = false)
    {
        $users = $this->userRepository->GetAllRegisteredUsers();
        foreach ($users as $userRec){
            assert($userRec instanceof User);
            if($isOnlyAgent){
                if($userRec->getRole() === (UsersTypes::AGENT || UsersTypes::SUPER_AGENT)){
                    $userNot = new NotificationUser();
                    $userNot->setUser($userRec);
                    $userNot->setNotification($notification);
                    $userNot->setIsRead(false);
                    $userNot->setCreatedAt(new \DateTimeImmutable());
                    $this->notificationUserRepository->save($userNot,true);
                }
            } else {
                $userNot = new NotificationUser();
                $userNot->setUser($userRec);
                $userNot->setNotification($notification);
                $userNot->setIsRead(false);
                $userNot->setCreatedAt(new \DateTimeImmutable());
                $this->notificationUserRepository->save($userNot,true);
            }

        }
    }




}
