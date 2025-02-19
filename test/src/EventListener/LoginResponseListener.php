<?php
namespace App\EventListener;

use App\Entity\User;
use App\Repository\AtarimRepository;
use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class LoginResponseListener
{
    private $tokenStorage;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        UserRepository $userRepository,
    )
    {
        $this->tokenStorage = $tokenStorage;
        $this->userRepository = $userRepository;
    }

    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event)
    {
        $data = $event->getData();
        $token = $this->tokenStorage->getToken();
        if ($token) {
            $user = $token->getUser();
            assert($user instanceof User);
            if ($user instanceof UserInterface) {
                if ($user->getIsBlocked()) {
                    $data['status'] = 'error';
                    $data['message'] = 'User is blocked';
                    $data['token'] = null;
                    $event->setData($data);
                } else {
                    $data['status'] = 'success';
                    $data['user'] = [
                        'id' => $user->getId(),
                        'extId' => $user->getExtId(),
                        'name' => $user->getName(),
                        'email' => $user->getEmail(),
                        'phone' => $user->getPhone(),
                        'roles' => $user->getRoles(),
                        'role' => $user->getRole(),
                        'hp' => $user->getHp(),
                        'payCode' => $user->getPayCode(),
                        'payDesc' => $user->getPayDes(),
                        'maxCredit' => $user->getMaxCredit(),
                        'maxObligo' => $user->getMaxObligo(),
                        'taxCode' => $user->getTaxCode(),
                        'createdAt' => $user->getCreatedAt(),
                        'updatedAt' => $user->getUpdatedAt(),
                        'city' => $user->getCity(),
                        'address' => $user->getAddress(),
                    ];
                    $event->setData($data);
                }
            }
        }
    }
}