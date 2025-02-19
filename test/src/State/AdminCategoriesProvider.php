<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;

use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use App\Erp\Core\ErpManager;
use App\Repository\CategoryRepository;
use App\Repository\UserRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AdminCategoriesProvider implements ProviderInterface
{
    public function __construct(
        private readonly RequestStack $requestStack,
        #[Autowire(service: CollectionProvider::class)] private ProviderInterface $collectionProvider,
        private readonly UserRepository $userRepository,
        private readonly CategoryRepository $categoryRepository,

    )
    {

    }


    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $lvl1 =  $this->requestStack->getCurrentRequest()->attributes->get('lvl1');
        $lvl2 =  $this->requestStack->getCurrentRequest()->attributes->get('lvl2');
        $response = $this->categoryRepository->GetAdminCategories($lvl1,$lvl2);
        return $response;
    }
}
