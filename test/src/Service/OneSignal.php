<?php

namespace App\Service;

use App\Entity\User;
use App\Enum\UsersTypes;
use App\Repository\UserRepository;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class OneSignal
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly UserRepository $userRepository,
    )
    {
        $this->appId = $_ENV['ONE_SIGNAL_APP_ID'] ;
        $this->apiKey = $_ENV['ONE_SIGNAL_KEY'] ;
    }

    public function SendPushAllUser(string $title, string $description, string $imgLink)
    {
        $users = $this->userRepository->GetAllOneSignalAppIds();
        $appIds = [];
        foreach ($users as $user) {
            assert($user instanceof User);
            if($user->getOneSignalAppId()){
                $appIds[] = $user->getOneSignalAppId();
            }
        }
        $object = $this->PrepareObject($title,$description,$appIds,$imgLink);
        return $this->SendOneSignal($object);
    }

    public function SendOrderPush(User $user, string $title, string $description)
    {
        if($user->getOneSignalAppId()){
            $object = $this->PrepareObject($title, $description, [$user->getOneSignalAppId()],'');
            $this->SendOneSignal($object);
        }

        return $this;
    }

    public function AlertToAgentsGetOrder(string $title, string $description)
    {
        $users = $this->userRepository->GetAllOneSignalAppIds();
        $appIds = [];
        foreach ($users as $user) {
            assert($user instanceof User);
            if($user->getOneSignalAppId() && ( $user->getRole() === UsersTypes::AGENT || $user->getRole() === UsersTypes::SUPER_AGENT || $user->getRole() === UsersTypes::ADMIN)){
                $appIds[] = $user->getOneSignalAppId();
            }
        }
        $object = $this->PrepareObject($title,$description,$appIds,'');
        $this->SendOneSignal($object);
        return $this;
    }

    public function SendAllAgents(string $title, string $description, string $imgLink)
    {
        $users = $this->userRepository->GetAllOneSignalAppIds();
        $appIds = [];
        foreach ($users as $user) {
            assert($user instanceof User);
            if($user->getOneSignalAppId() && ( $user->getRole() === UsersTypes::AGENT || $user->getRole() === UsersTypes::SUPER_AGENT)){
                $appIds[] = $user->getOneSignalAppId();
            }
        }
        $object = $this->PrepareObject($title,$description,$appIds,$imgLink);
        return $this->SendOneSignal($object);
    }

    private function PrepareObject(string $title, string $description, array $appIds, string $img)
    {
        $headingObj = new \stdClass();
        $headingObj->en = $title;

        $contentsObj = new \stdClass();
        $contentsObj->en = $description;

        $fields = array(
            'app_id' => $this->appId,
            'include_player_ids' => $appIds,
            'headings' =>  $headingObj,
            'contents' => $contentsObj,
            'chrome_web_image' => $img ?? null
        );

        return $fields;
    }

    private function SendOneSignal($obj)
    {
        try {
            $response = $this->httpClient->request(
                'POST',
                "https://onesignal.com/api/v1/notifications",
                [
                    'headers' => [
                        'Content-Type: application/json; charset=utf-8',
                    ],
                    'body' => json_encode($obj)
                ]
            );

            $statusCode = $response->getStatusCode();
            $contentType = $response->getHeaders()['content-type'][0];
            $content = $response->getContent();
            $content = $response->toArray();
            return $content;
        } catch (\Exception $exception) {
            return 'error';
        }

    }
}