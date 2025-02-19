<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Entity\NotificationUser;
use App\Entity\User;
use App\Repository\NotificationRepository;
use App\Repository\NotificationUserRepository;
use App\Repository\UserRepository;
use phpDocumentor\Reflection\PseudoTypes\IntegerRange;
use PHPUnit\Util\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NotificationController extends AbstractController
{
    public function __construct(
        private readonly NotificationRepository $notificationRepository,
        private readonly NotificationUserRepository $notificationUserRepository,
        private readonly UserRepository $userRepository,
    )
    {}

    #[Route('/notificationHandler/{notificationId}/{type}', name: 'app_notification')]
    public function index($notificationId,$type): Response
    {
        $notification = $this->notificationRepository->findOneById($notificationId);
        if(!$notification) throw new Exception('not found notification id');

        $response = null;

        if($type == 'allUsers'){
           $response =  $this->handleAllUsers($notification);
        }

        if($type == 'allAgents') {
            $response =  $this->handleAllAgents($notification);
        }

        return $this->json($response);
    }


    private function handleAllUsers(Notification $notification)
    {
        $allRegisteredUsers = $this->userRepository->GetUnregisteredUsers();
        foreach ($allRegisteredUsers as $userRec){
            assert($userRec instanceof User);
            $newNotificationUser = new NotificationUser();
            $newNotificationUser->setUser($userRec);
            $newNotificationUser->setNotification($notification);
            $newNotificationUser->setIsRead(false);
            $newNotificationUser->setCreatedAt(new \DateTimeImmutable());
            $this->notificationUserRepository->save($newNotificationUser,true);
        }
        return 'ok';
    }

    private function handleAllAgents(Notification $notification)
    {
        $allRegisteredUsers = $this->userRepository->GetAllAgents();
        foreach ($allRegisteredUsers as $userRec){
            assert($userRec instanceof User);
            $newNotificationUser = new NotificationUser();
            $newNotificationUser->setUser($userRec);
            $newNotificationUser->setNotification($notification);
            $newNotificationUser->setIsRead(false);
            $newNotificationUser->setCreatedAt(new \DateTimeImmutable());
            $this->notificationUserRepository->save($newNotificationUser,true);
        }
        return 'ok';
    }
}
