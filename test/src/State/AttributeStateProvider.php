<?php

namespace App\State;

use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Erp\Core\ErpManager;
use App\Repository\AttributeMainRepository;
use App\Repository\UserRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;

class AttributeStateProvider implements ProviderInterface
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly AttributeMainRepository $attributeMainRepository,
        #[Autowire(service: CollectionProvider::class)] private ProviderInterface $collectionProvider,
        private readonly UserRepository $userRepository,
        private readonly ErpManager $erpManager,
    )
    {
        $this->isUsedMigvan = $_ENV['IS_WITH_MIGVAN'] === "true";
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
            $userExtId = $this->requestStack->getCurrentRequest()->get('userExtId');
            $search = $this->requestStack->getCurrentRequest()->get('search');

            $migvanOnline = [];
            if($this->isUsedMigvan && $userExtId) {
                $migvanOnline = $this->erpManager->GetMigvanOnline($userExtId)->migvans;
                if(count($migvanOnline) == 0) {
                    $userExtId = null;
                }
            } else {
                $userExtId = null;
            }

            $lvl1 = $this->requestStack->getCurrentRequest()->attributes->get('lvl1');
            $lvl2 = $this->requestStack->getCurrentRequest()->attributes->get('lvl2');
            $lvl3 = $this->requestStack->getCurrentRequest()->attributes->get('lvl3');
            $response = $this->attributeMainRepository->findAttributesByCategoryExistProducts($lvl1,$lvl2,$lvl3, $userExtId, $migvanOnline,$search);
            return $response;


    }
}
