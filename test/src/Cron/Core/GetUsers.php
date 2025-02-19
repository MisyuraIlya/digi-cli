<?php

namespace App\Cron\Core;
use App\Entity\User;
use App\Enum\UsersTypes;
use App\Erp\Core\ErpManager;
use App\Repository\UserRepository;

class GetUsers
{

    public function __construct(
        private readonly UserRepository $repository,
        private readonly ErpManager $erpManager,
    )
    {}

    private function SyncChildren(array $childrens, User $parent)
    {
        foreach ($childrens as $itemRec) {
            $user = $this->repository->findOneByExIdAndPhone($itemRec->userExId, $itemRec->phone);
            if($itemRec->userExId) {
                if(empty($user)){
                    $user = new User();
                    $user->setExtId($itemRec->userExId);
                    $user->setPhone($itemRec->phone);
                    $user->setCreatedAt(new \DateTimeImmutable());
                    $user->setIsRegistered(false);
                    $user->setParent($parent);
                }
                $user->setIsAgent(false);
                $user->setRoles(UsersTypes::USER);
                $user->setRole(UsersTypes::USER);
                $user->setIsBlocked($itemRec->isBlocked);
                $user->setUpdatedAt(new \DateTimeImmutable());
                $user->setName($itemRec->name);
                $user->setIsAllowOrder(true);
                $user->setIsAllowAllClients(false);
                $user->setMaxCredit($itemRec->maxCredit);
                $user->setIsVatEnabled($itemRec->isVatEnabled);
                $user->setMaxObligo($itemRec->maxObligo);
                $user->setPayCode($itemRec->payCode);
                $user->setPayDes($itemRec->payDes);
                $user->setHp($itemRec->hp);
                $user->setTaxCode($itemRec->taxCode);
                $user->setSearch($itemRec->userExId . ' ' . $itemRec->name);
                if($itemRec->agentCode){
                    $agent = $this->repository->findOneByExtIdAndAgent($itemRec->agentCode);
                    if($agent){
                        $user->setAgentId($agent);
                    }
                }
                $this->repository->createUser($user, true);
            }
        }
    }

    public function sync()
    {

        $response = $this->erpManager->GetUsers();
        foreach ($response->users as $itemRec) {
            try {
                if(!$itemRec->isBlocked){
                    $user = $this->repository->findOneBy(['extId' => $itemRec->userExId, 'phone' => $itemRec->phone]);
                    if($itemRec->userExId) {
                        if(empty($user)){
                            $user = new User();
                            $user->setExtId($itemRec->userExId);
                            $user->setPhone($itemRec->phone);
                            $user->setCreatedAt(new \DateTimeImmutable());
                            $user->setIsRegistered(false);
                        }
                        $user->setIsAgent(false);
                        $user->setRoles(UsersTypes::USER);
                        $user->setRole(UsersTypes::USER);
                        $user->setIsBlocked($itemRec->isBlocked);
                        $user->setCity($itemRec->town);
                        $user->setIsVatEnabled($itemRec->isVatEnabled);
                        $user->setUpdatedAt(new \DateTimeImmutable());
                        $user->setName($itemRec->name);
                        $user->setIsAllowOrder(true);
                        $user->setIsAllowAllClients(false);
                        $user->setMaxCredit($itemRec->maxCredit);
                        if($itemRec->maxObligo){
                            $user->setMaxObligo($itemRec->maxObligo);
                        }
                        $user->setPayCode($itemRec->payCode);
                        $user->setPayDes($itemRec->payDes);
                        $user->setHp($itemRec->hp);
                        $user->setTaxCode($itemRec->taxCode);
                        $user->setSearch($itemRec->userExId . ' ' . $itemRec->name);
                        if($itemRec->agentCode){
                            $agent = $this->repository->findOneByExtIdAndAgent($itemRec->agentCode);
                            if($agent){
                                $user->setAgentId($agent);
                            }
                        }
                        $this->repository->createUser($user, true);

                        $this->SyncChildren($itemRec->subUsers,$user);
                    }
                }

            } catch (\Exception $exception){
            }

        }
    }
}