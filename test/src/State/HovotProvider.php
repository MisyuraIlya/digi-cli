<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Erp\Core\ErpManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class HovotProvider implements ProviderInterface
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly ErpManager $erpManager,
    )
    {
        $this->fromDate= $this->requestStack->getCurrentRequest()->attributes->get('dateFrom');
        $this->toDate = $this->requestStack->getCurrentRequest()->attributes->get('dateTo');
        $this->userExtId = $this->requestStack->getCurrentRequest()->attributes->get('userExtId');
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        return $this->GetHandler($operation,$uriVariables,$context);
    }


    private function GetHandler($operation,$uriVariables,$context)
    {
        $format = "Y-m-d";
        $dateFrom = \DateTimeImmutable::createFromFormat($format, $this->fromDate);
        $dateTo = \DateTimeImmutable::createFromFormat($format, $this->toDate);
        $response = $this->erpManager->GetHovot(
            $this->userExtId,
            $dateFrom,
            $dateTo,
        );

        return $response;
    }
}
