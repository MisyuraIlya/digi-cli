<?php

namespace App\State;

use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use App\Entity\History;
use App\Enum\DocumentsTypePriority;
use App\Enum\DocumentsTypeSap;
use App\Erp\Core\Dto\DocumentDto;
use App\Erp\Core\Dto\DocumentsDto;
use App\Erp\Core\ErpManager;
use App\Repository\HistoryRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DocumentsProvider implements ProviderInterface
{
    private $userPriceLists = [];

    public function __construct(
        private readonly RequestStack $requestStack,
        private Pagination $pagination,
        private readonly ProductRepository $productRepository,
        private readonly UserRepository $userRepository,
        private readonly ErpManager $erpManager,
        private readonly HistoryRepository $historyRepository,
        private NormalizerInterface $normalizer,
    )
    {
        $docType = $this->requestStack->getCurrentRequest()->attributes->get('documentType');
        $docType = trim($docType);
        $this->documentType = $this->GetType($docType);
        $this->fromDate = $this->requestStack->getCurrentRequest()->attributes->get('dateFrom');
        $this->toDate = $this->requestStack->getCurrentRequest()->attributes->get('dateTo');
        $this->userId = $this->requestStack->getCurrentRequest()->query->get('userId');
        $this->userDb = $this->userRepository->findOneById($this->userId);
        $this->limit = $this->requestStack->getCurrentRequest()->query->get('limit') ?? 10;
        $this->documentNumber = $this->requestStack->getCurrentRequest()->attributes->get('documentNumber');
        $this->page = $this->requestStack->getCurrentRequest()->query->get('page') ?? 1;
        $this->search = $this->requestStack->getCurrentRequest()->query->get('search');
        $this->toSlice = $_ENV['ERP_TYPE'] === 'SAP' ? false : true;
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if ($operation instanceof CollectionOperationInterface) {
            $currentPage = $this->pagination->getPage($context);
            $result = $this->CollectionHandler($operation, $uriVariables, $context);
            $start = ($currentPage - 1) * $this->limit;
            if ($result['slice']) {
                $result['result'] = array_slice($result['result'], $start, $this->limit);
            }
            return new TraversablePaginator(
                new \ArrayIterator($result['result']),
                $currentPage,
                $this->limit,
                $result['totalCount'],
            );
        }
        return $this->GetHandler($operation, $uriVariables, $context);
    }

    private function CollectionHandler($operation, $uriVariables, $context)
    {
        $format = "Y-m-d";
        $dateFrom = \DateTimeImmutable::createFromFormat($format, $this->fromDate);
        $dateTo = \DateTimeImmutable::createFromFormat($format, $this->toDate);
        $page = $this->pagination->getPage($context);
        $documentTypes = [
            DocumentsTypePriority::ORDERS->value ,
            DocumentsTypePriority::PRICE_OFFER->value,
            DocumentsTypePriority::DELIVERY_ORDER->value,
            DocumentsTypePriority::AI_INVOICE->value,
            DocumentsTypePriority::CI_INVOICE->value,
            DocumentsTypePriority::RETURN_ORDERS->value,
            DocumentsTypeSap::ORDERS->value,
            DocumentsTypeSap::PRICE_OFFER->value,
            DocumentsTypeSap::DELIVERY_ORDER->value,
            DocumentsTypeSap::INVOICES->value,
            DocumentsTypeSap::RETURN_ORDERS->value,
        ];

        $normalizedDocumentType = strtolower(trim($this->documentType));
        $documentTypesNormalized = array_map('strtolower', $documentTypes);
        $response = null;
        if (in_array($normalizedDocumentType, $documentTypesNormalized)) {
            $response = $this->erpManager->GetDocuments(
                $dateFrom,
                $dateTo,
                $this->documentType,
                $this->limit,
                (int) $this->page,
                $this->userDb,
                $this->search
            );
            return [
                "result" =>  $this->HandleResultWithUsers($response->documents),
                "totalCount" => $response->totalRecords,
                'slice' => $this->toSlice
            ];
        } elseif ($this->documentType == DocumentsTypePriority::HISTORY->value || $this->documentType == DocumentsTypePriority::DRAFT->value || $this->documentType == DocumentsTypePriority::APPROVE->value) {

            $history = $this->historyRepository->historyHandler($dateFrom, $dateTo, $this->userId, $page, $this->limit);
            return [
                "result" => $this->HandleResultWithUsers($this->ConvertHistoryToDocumentsDto($history['result'])->documents),
                "totalCount" => $history['totalCount'],
                'slice' => $this->toSlice
            ];
        }

        throw new \Exception("Unknown document type");
    }

    private function GetHandler($operation, $uriVariables, $context)
    {
        if ($this->documentType == DocumentsTypePriority::HISTORY || $this->documentType == DocumentsTypePriority::DRAFT || $this->documentType == DocumentsTypePriority::APPROVE) {
            $response = $this->historyRepository->historyItemHandler($this->documentNumber);
            $response = $this->ConvertHistoryItemToDocumentItemsDto($response);
        } else {
            $response = $this->erpManager->GetDocumentsItem($this->documentNumber, $this->documentType);
        }

        $makats = [];
        foreach ($response->products as &$itemRec) {
            $findProd = $this->productRepository->findOneBySkuAndToArray($itemRec->sku);
            $findProdPacacakge = $this->productRepository->findOneBySku($itemRec->sku);

            if (!empty($findProd) && $findProd[0]) {
                $makats[] = $findProd[0]['sku'];
                $itemRec->product = $findProd[0];
            }
        }

        return $response;
    }

    private function ConvertHistoryToDocumentsDto(array $histoires): DocumentsDto
    {
        $result = new DocumentsDto();
        $result->documents = [];
        foreach ($histoires as $histoire) {
            assert($histoire instanceof History);
            $obj = new DocumentDto();
            $obj->id = $histoire->getId();
            $obj->documentNumber = $histoire->getOrderExtId() ?? $histoire->getId();
            $obj->documentType = $histoire->getDocumentType()->value;
            $obj->userName = $histoire->getUser() ? $histoire->getUser()->getName() : '';
            $obj->userExId = $histoire->getUser() ? $histoire->getUser()->getExtId() : '';
            if(!empty( $histoire->getAgent())){
                $obj->agentExId = $histoire->getAgent()->getExtId() ?? '';
                $obj->agentName = $histoire->getAgent()->getName() ?? '';
            } else {
                $obj->agentExId = null;
                $obj->agentName = null;
            }
            $obj->status = $histoire->getOrderStatus();
            $obj->createdAt = $histoire->getCreatedAt();
            $obj->updatedAt = $histoire->getUpdatedAt();
            $obj->total = $histoire->getTotal();
            $obj->error = $histoire->getError();
            $result->documents[] = $obj;
        }
        return $result;
    }

    private function ConvertHistoryItemToDocumentItemsDto(History $history): DocumentItemsDto
    {
        $result = new DocumentItemsDto();
        $result->totalPriceAfterTax = $history->getTotal();
        $result->documentType = $history->getDocumentType()->value;
        $result->totalPrecent = $history->getDiscount();
        $result->totalAfterDiscount = 0;
        $result->totalTax = $history->getTax();
        $result->products = [];
        foreach ($history->getHistoryDetaileds() as $productRec){
            $obj = new DocumentItemDto();
            $obj->sku = $productRec->getProduct()->getSku();
            $obj->title = $productRec->getProduct()->getTitle();
            $obj->quantity = $productRec->getQuantity();
            $obj->priceByOne = $productRec->getSinglePrice();
            $obj->total = $productRec->getTotal();
            $obj->discount = $productRec->getDiscount();
            $result->products[] = $obj;
        }
        return $result;
    }

    private function HandleResultWithUsers(array $result): array
    {
        foreach ($result as &$item) {
            $user = $this->userRepository->findOneBy(['extId' => $item->userExId]);
            if ($user) {
                $item->user = $this->normalizer->normalize($user, null, ['groups' => ['user:read']]);
            } else {
                $item->user = null;
            }
        }
        return $result;
    }

    private function GetType(string $value): DocumentsType
    {
        $enumDetails = DocumentsType::getAllDetails();
        if (isset($enumDetails[$value]['ENGLISH'])) {
            return $enumDetails[$value]['ENGLISH'];
        }

        throw new \InvalidArgumentException("Invalid DocumentsType: $value");
    }
}
