<?php

namespace App\Controller;

use App\Entity\User;
use App\Enum\UsersTypes;
use App\Erp\Core\ErpManager;
use App\helpers\ApiResponse;
use App\Repository\UserRepository;
use App\Service\SmsHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AuthController extends AbstractController
{
    public function __construct(
        private readonly UserPasswordHasherInterface $hasher,
        private readonly UserRepository $repository,
        private readonly SmsHandler $smsHandler,
        private readonly ErpManager $erpManager
    ){}

    #[Route('/auth/registration', name: 'app_auth_register', methods: ['PUT'])]
    public function register(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);
            $exId = $data['extId'];
            $username = $data['username'];
            $password = $data['password'];
            $phone = $data['phone'];
            $token = $data['token'];

            $findUser = $this->repository->findOneByExIdAndPhone($exId, $phone);
            if(!$findUser) throw new \Exception('לא נמצא לקוח');
            if($findUser->getIsBlocked()) throw new \Exception('לקוח חסום');
            if($findUser->getIsRegistered()) throw new \Exception('לקוח רשום');
            if($findUser->getRecovery() != $token) throw new \Exception('קוד סודי שגוי');

            if(!empty($findUser)){
                $findUser->setIsRegistered(true);
                $findUser->setEmail($username);
                $findUser->setUpdatedAt(new \DateTimeImmutable());
                $findUser->setRecovery(random_int(100000,900000));
                $findUser->setPasswordUnencrypted($password);
                $findUser->setPassword($this->hasher->hashPassword($findUser, $password));
                $this->repository->createUser($findUser, true);
            }

            return $this->json((new ApiResponse(null,'user created'))->OnSuccess());
        } catch (\Exception $e) {
            return $this->json((new ApiResponse(null,'שגיאה ' . $e->getMessage()))->OnError());
        }
    }

    #[Route('/auth/validation', name: 'app_auth_validation', methods: ['POST'])]
    public function validation(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);
            $exId = $data['exId'];
            $phone = $data['phone'];
            $findUser = $this->repository->findOneByExIdAndPhone($exId, $phone);
            if(!$findUser) throw new \Exception('לא נמצא לקוח');
            $recovery = random_int(100000,900000);
            $findUser->setRecovery($recovery);
            $this->repository->createUser($findUser,true);
            if(!$findUser) throw new \Exception('לא נמצא לקוח');
            if($findUser->getIsBlocked()) throw new \Exception('לקוח חסום');
            if($findUser->getIsRegistered()) throw new \Exception('לקוח רשום');
//            $this->smsHandler->SendSms($phone, 'קוד סודי להרשמה: '.$recovery);
            $response = [
                "exId" => $findUser->getExtId(),
                "name" => $findUser->getName()
            ];
            return $this->json((new ApiResponse($response,"נשלח קוד סודי לאימות"))->OnSuccess());
        } catch (\Exception $e) {
            return $this->json((new ApiResponse(null, $e->getMessage()))->OnError());
        }
    }

    #[Route('/auth/restorePasswordStepOne', name: 'app_auth_restorePasswordStepOne', methods: ['POST'])]
    public function restorePasswordStepOne(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);
            $email = $data['email'];
            $findUser = $this->repository->findOneByEmail($email);
            if(!$findUser) throw new \Exception('לא נמצא לקוח');
            if($findUser->getIsBlocked()) throw new \Exception('לקוח חסום');
            $recovery = random_int(10000,90000);
            $findUser->setRecovery($recovery);
            $this->repository->createUser($findUser,true);
            $this->smsHandler->SendSms($findUser->getPhone(), 'קוד סודי לשחזור סיסמא: '.$recovery);

            return $this->json((new ApiResponse(null,"נשלח קוד סודי לאימות"))->OnSuccess());
        } catch (\Exception $e) {
            return $this->json((new ApiResponse(null, $e->getMessage()))->OnError());
        }
    }

    #[Route('/auth/restorePasswordStepTwo', name: 'app_auth_restorePasswordStepTwo', methods: ['POST'])]
    public function restorePasswordStepTwo(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);
            $email = $data['email'];
            $token = $data['token'];
            $password = $data['password'];
            $findUser = $this->repository->findOneByEmail($email);
            if(!$findUser) throw new \Exception('לא נמצא לקוח');
            if($findUser->getIsBlocked()) throw new \Exception('לקוח חסום');
            if($findUser->getRecovery() !== $token) throw new \Exception('קוד סודי אינו תקין');
            $findUser->setPassword($this->hasher->hashPassword($findUser, $password));
            $findUser->setPasswordUnencrypted($password);
            $this->repository->createUser($findUser,true);
            return $this->json((new ApiResponse(null,"סיסמא שונתה בהצלחה"))->OnSuccess());
        } catch (\Exception $e) {
            return $this->json((new ApiResponse(null, $e->getMessage()))->OnError());
        }
    }

    #[Route('/auth/createUser', name: 'app_auth_createUser', methods: ['POST'])]
    public function createUser(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);
            $email = $data['email'];
            $password = $data['password'];
            $phone = $data['phone'];
            $fullName = $data['fullName'];
            $hp = $data['hp'];
            $company = $data['company'];
            //TODO ADD COMPANY NAME TO USERINFO ENTITY
            $town = $data['town'];
            $address = $data['address'];

            $newUser = new User();
            $newUser->setName($fullName);
            $newUser->setCreatedAt(new \DateTimeImmutable());
            $newUser->setUpdatedAt(new \DateTimeImmutable());
            $newUser->setIsBlocked(false);
            $newUser->setRoles(UsersTypes::USER);
            $newUser->setRole(UsersTypes::USER);
            $newUser->setRecovery(random_int(900000,999999));
            $newUser->setEmail($email);
            $newUser->setPhone($phone);
            $newUser->setIsRegistered(true);
            $newUser->setExtId('999999');
            $newUser->setPasswordUnencrypted($password);
            $newUser->setIsAllowAllClients(false);
            $newUser->setIsAllowOrder(false);
            $newUser->setPassword($this->hasher->hashPassword($newUser,$password));
            $this->repository->createUser($newUser,true);

            return $this->json((new ApiResponse(null,"לקוח נוצר בהצלחה!"))->OnSuccess());
        } catch (\Exception $e) {
            return $this->json((new ApiResponse(null, $e->getMessage()))->OnError());
        }
    }

    #[Route('/auth/createAgent', name: 'app_auth_createAgent', methods: ['POST'])]
    public function createAgent(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);
            $extId = $data['extId'];
            $email = $data['email'];
            $password = $data['password'];
            $phone = $data['phone'];
            $name = $data['name'];
            $isAllowOrder = $data['isAllowOrder'];
            $isAllowClients = $data['isAllowAllClients'];


            $newUser = new User();
            $newUser->setExtId($extId);
            $newUser->setName($name);
            $newUser->setCreatedAt(new \DateTimeImmutable());
            $newUser->setUpdatedAt(new \DateTimeImmutable());
            $newUser->setIsBlocked(false);
            $newUser->setRoles(UsersTypes::AGENT);
            $newUser->setRole(UsersTypes::AGENT);
            $newUser->setRecovery(random_int(900000,999999));
            $newUser->setEmail($email);
            $newUser->setPhone($phone);
            $newUser->setIsRegistered(true);
            $newUser->setPassword($this->hasher->hashPassword($newUser,$password));
            $newUser->setPasswordUnencrypted($password);
            $newUser->setIsAllowAllClients($isAllowClients);
            $newUser->setIsAllowOrder($isAllowOrder);
            $this->repository->createUser($newUser,true);

            return $this->json((new ApiResponse(null,"סוכן נוצר בהצלחה!"))->OnSuccess());
        } catch (\Exception $e) {
            return $this->json((new ApiResponse(null, $e->getMessage()))->OnError());
        }
    }

    #[Route('/auth/updateUser', name: 'app_auth_updateUser', methods: ['POST'])]
    public function updateUser(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);
            $id = $data['id'];
            $extId = $data['extId'] ?? null;
            $email = $data['email'] ?? null;
            $password = $data['passwordUnencrypted'] ?? null;
            $phone = $data['phone'] ?? null;
            $name = $data['name'] ?? null;
            $isAllowOrder = $data['isAllowOrder'] ?? false;
            $isAllowClients = $data['isAllowAllClients']  ?? false;
            $isBlocked = $data['isBlocked']  ?? false;
            $isRegistered = $data['isRegistered']  ?? false;
            $role = $data['role'] ?? UsersTypes::USER;
            if($role == 'ROLE_AGENT'){
                $role = UsersTypes::AGENT;
            } else if($role == 'ROLE_USER'){
                $role = UsersTypes::USER;
            } else if($role == 'ROLE_SUPER_AGENT') {
                $role = UsersTypes::SUPER_AGENT;
            } else {
                $role = UsersTypes::AGENT;
            }
            $findUser = $this->repository->findOneById($id);
            if(!$findUser) throw new \Exception('לא נמצא לקוח');

            $findUser->setExtId($extId);
            $findUser->setName($name);
            $findUser->setIsRegistered($isRegistered);
            $findUser->setUpdatedAt(new \DateTimeImmutable());
            $findUser->setIsBlocked($isBlocked);
            $findUser->setRole($role);
            $findUser->setEmail($email);
            $findUser->setPhone($phone);
            if($password) {
                $findUser->setPassword($this->hasher->hashPassword($findUser,$password));
                $findUser->setPasswordUnencrypted($password);
            } else {
                $findUser->setPassword(null);
                $findUser->setPasswordUnencrypted(null);
            }

            $findUser->setIsAllowAllClients($isAllowClients);
            $findUser->setIsAllowOrder($isAllowOrder);
            try {
                $this->repository->createUser($findUser,true);
            } catch (\Exception $exception){
                throw new \Exception('לקוח עם מייל כזה נמצא');
            }

            return $this->json((new ApiResponse(null,"יוזר עודכן בהצלחה!"))->OnSuccess());
        } catch (\Exception $e) {
            return $this->json((new ApiResponse(null, $e->getMessage()))->OnError());
        }
    }

    #[Route('/user/moneyInfo/{userExtId}', name: 'user_money_info', methods: ['GET'])]
    public function userMoneyInfo(Request $request,$userExtId): Response
    {
        try {
            $response = $this->erpManager->UserMoneyInfo($userExtId);
            return $this->json($response->lines);
        } catch (\Exception $e) {
            return $this->json((new ApiResponse(null, $e->getMessage()))->OnError());
        }
    }

}
