<?php

namespace App\Erp\Core\Priority;

use App\Entity\History;
use App\Entity\HistoryDetailed;
use App\Entity\User;
use App\Enum\DocumentsTypePriority;
use App\Enum\HistoryDocumentTypeEnum;
use App\Erp\Core\Dto\AgentStatisticDto;
use App\Erp\Core\Dto\BonusesDto;
use App\Erp\Core\Dto\CartessetDto;
use App\Erp\Core\Dto\CategoriesDto;
use App\Erp\Core\Dto\CategoryDto;
use App\Erp\Core\Dto\DocumentItemsDto;
use App\Erp\Core\Dto\DocumentsDto;
use App\Erp\Core\Dto\HovotDto;
use App\Erp\Core\Dto\HovotLineDto;
use App\Erp\Core\Dto\MigvanDto;
use App\Erp\Core\Dto\MigvansDto;
use App\Erp\Core\Dto\MoneyInfo;
use App\Erp\Core\Dto\PackMainDto;
use App\Erp\Core\Dto\PackProductDto;
use App\Erp\Core\Dto\PacksMainDto;
use App\Erp\Core\Dto\PacksProductDto;
use App\Erp\Core\Dto\PriceListDetailedDto;
use App\Erp\Core\Dto\PriceListDto;
use App\Erp\Core\Dto\PriceListsDetailedDto;
use App\Erp\Core\Dto\PriceListsDto;
use App\Erp\Core\Dto\PriceListsUserDto;
use App\Erp\Core\Dto\PriceListUserDto;
use App\Erp\Core\Dto\PriceOnlineDto;
use App\Erp\Core\Dto\PricesDto;
use App\Erp\Core\Dto\ProductDto;
use App\Erp\Core\Dto\ProductsDto;
use App\Erp\Core\Dto\PurchaseHistory;
use App\Erp\Core\Dto\PurchaseHistoryItem;
use App\Erp\Core\Dto\SalesKeeperAlertDto;
use App\Erp\Core\Dto\SalesQuantityKeeperAlertDto;
use App\Erp\Core\Dto\SalesQuantityKeeperAlertLineDto;
use App\Erp\Core\Dto\StockDto;
use App\Erp\Core\Dto\StocksDto;
use App\Erp\Core\Dto\UserDto;
use App\Erp\Core\Dto\UsersDto;
use App\Erp\Core\ErpInterface;
use App\Repository\HistoryDetailedRepository;
use App\Repository\HistoryRepository;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Priority implements ErpInterface
{
    private string $username;
    private string $password;
    private string $url;

    public function __construct(string $url, string $username, string $password, HttpClientInterface $httpClient)
    {
        $this->username = $username;
        $this->password = $password;
        $this->url = $url;
        $this->httpClient = $httpClient;
        $erpType = $_ENV['ERP_TYPE'];
        $this->CategoryTable = $_ENV['CATEGORY_STATE'];
        $this->ErpCategoryLvl1 = $_ENV['CATEGORY_LVL_1'];
        $this->ErpCategoryLvl2 = $_ENV['CATEGORY_LVL_2'];
        $this->ErpCategoryLvl3 = $_ENV['CATEGORY_LVL_3'];
    }

    public function GetRequest($query)
    {
        try {
            $response = $this->httpClient->request(
                'GET',
                $this->url . $query,
                [
                    'auth_basic' => [$this->username, $this->password],
                    'http_version' => '1.1',
                    'timeout' => 600
                ]
            );
            $statusCode = $response->getStatusCode();
            $contentType = $response->getHeaders()['content-type'][0];
            $content = $response->getContent();
            $content = $response->toArray();
            return $content['value'];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function PostRequest(\stdClass $obj, string $table)
    {
        try {
            $response = $this->httpClient->request(
                'POST',
                $this->url . $table,
                [
                    'auth_basic' => [$this->username, $this->password],
                    'timeout' => 60,
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],
                    'body' => json_encode($obj)
                ]
            );

            return $response->toArray();

        } catch (\Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();
            $errorBody = $response->getContent(false);
            $message = "HTTP error occurred: $statusCode - $errorBody";
            // Handle or log the error as needed
            throw new \Exception($message);

        } catch (\Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();
            $errorBody = $response->getContent(false);
            $message = "HTTP error occurred: $statusCode - $errorBody";
            throw new \Exception($message);

        } catch (\Exception $e) {
            $message = $e->getMessage();
            throw new \Exception("An error occurred: $message");
        }
    }

    public function PatchRequest(object $obj, string $table)
    {
        $response = $this->httpClient->request(
            'PATCH',
            $this->url . $table,
            [
                'auth_basic' => [$this->username, $this->password],
                'timeout' => 60,

                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode($obj),
            ]
        );
        $statusCode = $response->getStatusCode();
        $contentType = $response->getHeaders()['content-type'][0];
        $content = $response->getContent();
        $content = $response->toArray();
        return $content;
    }

    public function GetDocuments(
        \DateTimeImmutable $dateFrom,
        \DateTimeImmutable $dateTo,
        string             $documentsType,
        int                $pageSize,
        int                $currentPage,
        ?User              $user,
        ?string            $search = null
    ): DocumentsDto
    {
        $data = [];
        if ($documentsType == DocumentsTypePriority::ORDERS->value) {
            $data = (new PriorityDocuments($this->url, $this->username, $this->password, $this->httpClient))->GetOrders($user, $dateFrom, $dateTo);
        } else if ($documentsType == DocumentsTypePriority::PRICE_OFFER->value) {
            $data = (new PriorityDocuments($this->url, $this->username, $this->password, $this->httpClient))->GetPriceOffer($user, $dateFrom, $dateTo);
        } else if ($documentsType == DocumentsTypePriority::DELIVERY_ORDER->value) {
            $data = (new PriorityDocuments($this->url, $this->username, $this->password, $this->httpClient))->GetDeliveryOrder($user, $dateFrom, $dateTo);
        } else if ($documentsType == DocumentsTypePriority::AI_INVOICE->value) {
            $data = (new PriorityDocuments($this->url, $this->username, $this->password, $this->httpClient))->GetAiInvoice($user, $dateFrom, $dateTo);
        } else if ($documentsType == DocumentsTypePriority::CI_INVOICE->value) {
            $data = (new PriorityDocuments($this->url, $this->username, $this->password, $this->httpClient))->GetCiInvoice($user, $dateFrom, $dateTo);
        } else if ($documentsType == DocumentsTypePriority::RETURN_ORDERS->value) {
            $data = (new PriorityDocuments($this->url, $this->username, $this->password, $this->httpClient))->GetReturnDocs($user, $dateFrom, $dateTo);
        }
        $obj = new DocumentsDto();
        $obj->documents = $data;
        return $obj;
    }

    public function GetDocumentsItem(string $documentNumber, string $documentType): DocumentItemsDto
    {
        if ($documentType == DocumentsTypePriority::ORDERS->value) {
            $orders = (new PriorityDocuments($this->url, $this->username, $this->password, $this->httpClient))->GetOrderItems($documentNumber);
            return $orders;
        }

        if ($documentType == DocumentsTypePriority::PRICE_OFFER->value) {
            $offers = (new PriorityDocuments($this->url, $this->username, $this->password, $this->httpClient))->GetPriceOfferItem($documentNumber);
            return $offers;
        }

        if ($documentType == DocumentsTypePriority::DELIVERY_ORDER->value) {
            $documentOrder = (new PriorityDocuments($this->url, $this->username, $this->password, $this->httpClient))->GetDeliveryOrderItem($documentNumber);
            return $documentOrder;
        }

        if ($documentType == DocumentsTypePriority::AI_INVOICE->value) {
            $aiInvoice = (new PriorityDocuments($this->url, $this->username, $this->password, $this->httpClient))->GetAiInvoiceItem($documentNumber);
            return $aiInvoice;
        }

        if ($documentType == DocumentsTypePriority::CI_INVOICE->value) {
            $ciInvoice = (new PriorityDocuments($this->url, $this->username, $this->password, $this->httpClient))->GetCiInvoiceItem($documentNumber);
            return $ciInvoice;
        }

        if ($documentType == DocumentsTypePriority::RETURN_ORDERS->value) {
            $returnDocItem = (new PriorityDocuments($this->url, $this->username, $this->password, $this->httpClient))->GetReturnDocsItem($documentNumber);
            return $returnDocItem;
        }

        return new DocumentItemsDto();
    }

    public function GetCartesset(string $userExId, \DateTimeImmutable $dateFrom, \DateTimeImmutable $dateTo): CartessetDto
    {
        $endpoint = "/ACCOUNTS_RECEIVABLE";
        $dateFrom = $dateFrom->format('Y-m-d\TH:i:s.u\Z');
        $dateTo = $dateTo->format('Y-m-d\TH:i:s.u\Z');
        $queryParameters = [
            '$filter' => "ACCNAME eq '$userExId'",
            '$expand' => 'ACCFNCITEMS2_SUBFORM($filter=BALDATE ge ' . $dateFrom . ' and BALDATE le ' . $dateTo . ')'
        ];
        $queryString = http_build_query($queryParameters);
        $urlQuery = $endpoint . '?' . $queryString;
        $response = $this->GetRequest($urlQuery);
        $result = new CartessetDto();
        $result->columns = [
            'תאריך',
            'ת.למאזן',
            'מס תנועה',
            'אסמכתא',
            'סוג תנועה',
            'פרטים',
            'חובה',
            'זכות',
            'יתרה',
        ];
        foreach ($response as $itemRec) {
            foreach ($itemRec['ACCFNCITEMS2_SUBFORM'] as $subRec) {
                $arr = [
                    $subRec['CURDATE'],
                    $subRec['BALDATE'],
                    $subRec['FNCNUM'],
                    $subRec['TODOREF'],
                    $subRec['FNCPATNAME'],
                    $subRec['DETAILS'],
                    $subRec['DEBIT'],
                    $subRec['CREDIT'],
                    $subRec['BAL'],
                ];
                $result->lines[] = $arr;
            }
        }

        return $result;

    }

    public function GetHovot(string $userExId, \DateTimeImmutable $dateFrom, \DateTimeImmutable $dateTo): HovotDto
    {
        $endpoint = "/ACCOUNTS_RECEIVABLE";
        $dateFrom = $dateFrom->format('Y-m-d\TH:i:s.u\Z');
        $dateTo = $dateTo->format('Y-m-d\TH:i:s.u\Z');
        $queryParameters = [
            '$filter' => "ACCNAME eq '$userExId'",
            '$expand' => 'ARFNCITEMS3_SUBFORM($filter=BALDATE ge ' . $dateFrom . ' and BALDATE le ' . $dateTo . ')'
        ];
        $queryString = http_build_query($queryParameters);
        $urlQuery = $endpoint . '?' . $queryString;
        $response = $this->GetRequest($urlQuery);
        $result = new HovotDto();
        foreach ($response as $itemRec) {
            $sum = 0;
            foreach ($itemRec['ARFNCITEMS3_SUBFORM'] as $subRec) {
                $obj = new HovotLineDto();
                $obj->createdAt = $subRec['CURDATE'];
                $obj->documentNumber = $subRec['IVNUM'];
                $obj->debit = $subRec['DEBIT1'];
                $sum += $subRec['DEBIT1'];
                $obj->lineSum = $sum;
                $obj->payDate = $subRec['FNCDATE'];
                $result->lines[] = $obj;
            }
            $result->total = $sum;
        }
        return $result;
    }

    public function PurchaseHistoryByUserAndSku(string $userExtId, string $sku): PurchaseHistory
    {
        $endpoint = "/ORDERS";
        $queryParameters = [
            '$filter' => "CUSTNAME eq '$userExtId'",
            '$select' => "ORDNAME,CURDATE",
            '$expand' => 'ORDERITEMS_SUBFORM($select=PARTNAME,TQUANT,PRICE,VPRICE,PERCENT,QPRICE,VATPRICE;$filter=PARTNAME eq ' . "'" . $sku . "'" . ')',
            '$top' => '200'
        ];
        $queryString = http_build_query($queryParameters);
        $urlQuery = $endpoint . '?' . $queryString;
        $response = $this->GetRequest($urlQuery);
        $result = new PurchaseHistory();
        foreach ($response as $itemRec) {
            foreach ($itemRec['ORDERITEMS_SUBFORM'] as $subRec) {
                $obj = new PurchaseHistoryItem();
                $obj->documentNumber = $itemRec['ORDNAME'];
                $obj->date = $itemRec['CURDATE'];
                $obj->quantity = $subRec['TQUANT'];
                $obj->price = $subRec['PRICE'];
                $obj->vatPrice = $subRec['VPRICE'];
                $obj->discount = $subRec['PERCENT'];
                $obj->totalPrice = $subRec['QPRICE'];
                $obj->vatTotal = $subRec['VATPRICE'];
                $result->items[] = $obj;
            }
        }
        return $result;
    }

    public function SendOrder(History $history): string
    {

        if ($history->getDocumentType() === HistoryDocumentTypeEnum::ORDER) {
            $response = $this->SendOrderTemplate($history);
        } elseif ($history->getDocumentType() === HistoryDocumentTypeEnum::QUOATE) {
            $response = $this->SendQuoteTemplate($history);
        } elseif ($history->getDocumentType() === HistoryDocumentTypeEnum::RETURN) {
            $response = $this->SendReturnTemplate($history);
        } else {
            throw new \Exception('לא נמצא מסמך כזה');
        }

        return $response;
    }

    private function SendOrderTemplate(History $history)
    {
        $obj = new \stdClass();
        $obj->CUSTNAME = $history->getUser()->getExtId();
        $obj->DUEDATE = $history->getDeliveryDate()->format('Y-m-d\TH:i:sP') ?? $history->getCreatedAt()->format('Y-m-d\TH:i:sP');
        $lines = new \stdClass();
        $lines->lines = [];
        foreach ($history->getHistoryDetaileds() as $itemRec) {
            assert($itemRec instanceof HistoryDetailed);
            $objLine = new \stdClass();
            $objLine->PARTNAME = $itemRec->getProduct()->getSku();
            $objLine->TQUANT = $itemRec->getQuantity();
            $objLine->PRICE = $itemRec->getSinglePrice();
            $lines->lines[] = $objLine;
        }

        $obj->ORDERITEMS_SUBFORM = $lines->lines;

        $response = $this->PostRequest($obj, '/ORDERS');
        if (isset($response['ORDNAME'])) {
            return $response['ORDNAME'];
        } else {
            throw new \Exception('הזמנה לא שודרה');
        }
    }

    private function SendQuoteTemplate(History $history)
    {
        $obj = new \stdClass();
        $obj->CUSTNAME = $history->getUser()->getExtId();
        $obj->PDATE = $history->getCreatedAt()->format('Y-m-d\TH:i:sP');
        $lines = new \stdClass();
        $lines->lines = [];
        foreach ($history->getHistoryDetaileds() as $itemRec) {
            assert($itemRec instanceof HistoryDetailed);
            $objLine = new \stdClass();
            $objLine->PARTNAME = $itemRec->getProduct()->getSku();
            $objLine->TQUANT = $itemRec->getQuantity();
            $objLine->PRICE = $itemRec->getSinglePrice();
            $lines->lines[] = $objLine;
        }

        $obj->CPROFITEMS_SUBFORM = $lines->lines;

        $response = $this->PostRequest($obj, '/' . 'CPROF');
        if (isset($response['CPROFNUM'])) {
            return $response['CPROFNUM'];
        } else {
            throw new \Exception('הזמנה לא שודרה');
        }
    }

    private function SendReturnTemplate(History $history)
    {
        $obj = new \stdClass();
        $obj->CUSTNAME = $history->getUser()->getExtId();
        $lines = new \stdClass();
        $lines->lines = [];
        foreach ($history->getHistoryDetaileds() as $itemRec) {
            $objLine = new \stdClass();
            $objLine->PARTNAME = $itemRec->getProduct()->getSku();
            $objLine->TQUANT = $itemRec->getQuantity();
            $objLine->PRICE = $itemRec->getTotal();
            $lines->lines[] = $objLine;
        }

        $obj->TRANSORDER_N_SUBFORM = $lines->lines;

        $response = $this->PostRequest($obj, '/DOCUMENTS_N');
        if (isset($response['DOCNO'])) {
            return $response['DOCNO'];
        } else {
            throw new \Exception('הזמנה לא שודרה');
        }
    }

    /** FOR CRON */
    public function GetProducts(?int $pageSize, ?int $skip): ProductsDto
    {
        $endpoint = "/LOGPART";
        if ($pageSize) {
            $queryExtras = [
                '$select' => "FTCODE,FTNAME,MINPRICE,PARTNAME,PARTDES,BARCODE,CONV,FAMILYNAME,FAMILYDES,SPEC9,SPEC10,STATDES,BASEPLPRICE,SPEC2,SPEC3,SPEC4,SPEC5,SPEC6,SPEC7,SPEC8,SPEC19,QAMT_PARTSPEC21",
                '$top' => $pageSize,
                '$skip' => $skip,
                '$filter' => "SHOWINWEB eq 'Y'"
            ];
        } else {
            $queryExtras = [
                '$select' => "FTCODE,FTNAME,MINPRICE,PARTNAME,PARTDES,BARCODE,CONV,FAMILYNAME,FAMILYDES,SPEC9,SPEC10,STATDES,BASEPLPRICE,SPEC2,SPEC3,SPEC4,SPEC5,SPEC6,SPEC7,SPEC8,SPEC19,QAMT_PARTSPEC21",
                '$filter' => "SHOWINWEB eq 'Y'"
            ];
        }

        $queryString = http_build_query($queryExtras);
        $urlQuery = $endpoint . '?' . $queryString;

        $dtoRes = new ProductsDto();
        $response = $this->GetRequest($urlQuery);
        foreach ($response as $itemRec) {
            $dto = new ProductDto();
            $dto->sku = $itemRec['PARTNAME'];
            $dto->title = $itemRec['QAMT_PARTSPEC21'];
            $dto->barcode = $itemRec['BARCODE'];
            $dto->packQuantity = $itemRec['CONV'];
            $dto->categoryLvl1Id = $itemRec[$this->ErpCategoryLvl1];
            $dto->categoryLvl1Name = $itemRec[$this->ErpCategoryLvl1 == 'FTCODE' ? 'FTNAME' : $this->ErpCategoryLvl1];
            $dto->categoryLvl2Id = $itemRec[$this->ErpCategoryLvl2];
            $dto->categoryLvl2Name = $itemRec[$this->ErpCategoryLvl2 == 'FAMILYNAME' ? 'FAMILYDES' : $this->ErpCategoryLvl2];
            $dto->categoryLvl3Id = $itemRec[$this->ErpCategoryLvl3];
            $dto->categoryLvl3Name = $itemRec[$this->ErpCategoryLvl3];
            $dto->status = $itemRec['STATDES'] === 'פעיל' ? true : false;
            $dto->baseprice = $itemRec['BASEPLPRICE'];
            $dto->minimumPrice = $itemRec['MINPRICE'];
            $dto->Extra2 = $itemRec['SPEC2'];
            $dto->Extra3 = $itemRec['SPEC3'];
            $dto->Extra4 = $itemRec['SPEC4'];
            $dto->Extra5 = $itemRec['SPEC5'];
            $dto->Extra6 = $itemRec['SPEC6'];
            $dto->Extra7 = $itemRec['SPEC7'];
            $dto->Extra8 = $itemRec['SPEC8'];
            if ($dto->categoryLvl1Id && $dto->categoryLvl1Name && $dto->categoryLvl2Id && $dto->categoryLvl2Name && $dto->categoryLvl3Id && $dto->categoryLvl3Name) {
                $dtoRes->products[] = $dto;
            }
        }
        return $dtoRes;
    }

    public function GetSubProducts(): ProductsDto
    {
        $endpoint = "/LOGPART";
        $queryExtras = [
            '$expand' => "PARTARC_SUBFORM"
        ];
        $queryString = http_build_query($queryExtras);
        $urlQuery = $endpoint . '?' . $queryString;

        $dtoRes = new ProductsDto();
        $response = $this->GetRequest($urlQuery);
        foreach ($response as $itemRec) {

            foreach ($itemRec['PARTARC_SUBFORM'] as $subRec) {
                $dto = new ProductDto();
                $dto->sku = $subRec['SONNAME'];
                $dto->title = $subRec['SONDES'];
                $dto->parent = $itemRec['PARTNAME'];
                $dtoRes->products[] = $dto;
            }
        }
        return $dtoRes;
    }

    public function GetUsers(): UsersDto
    {
//        $response = $this->GetRequest('/CUSTOMERS?$expand=CUSTDISCOUNT_SUBFORM');
        $response = $this->GetRequest('/CUSTOMERS?$expand=CUSTPERSONNEL_SUBFORM');
        $usersDto = new UsersDto();

        foreach ($response as $userRec) {
            $userDto = new UserDto();
            $userDto->userExId = $userRec['CUSTNAME'];
            $userDto->userDescription = $userRec['CUSTDES'];
            $userDto->name = $userRec['CUSTDES'];
            $userDto->isBlocked = $userRec['INACTIVEFLAG'] === 'Y' ? true : false;
            $userDto->phone = $userRec['PHONE'];
            $userDto->hp = $userRec['WTAXNUM'];
            $userDto->payCode = $userRec['PAYCODE'];
            $userDto->payDes = $userRec['PAYDES'];
            $userDto->maxCredit = $userRec['MAX_CREDIT'];
            $userDto->maxObligo = $userRec['MAX_OBLIGO'];
            $userDto->taxCode = $userRec['TAXCODE'];
            $userDto->agentCode = $userRec['AGENTCODE'];
            $userDto->subUsers = [];

            foreach ($userRec['CUSTPERSONNEL_SUBFORM'] as $subRec) {
                $subUsers = new UserDto();
                $subUsers->userExId = $userRec['CUSTNAME'];
                $subUsers->userDescription = $userRec['CUSTDES'];
                $subUsers->name = $subRec['NAME'];
                $subUsers->isBlocked = $userRec['INACTIVEFLAG'] === 'Y' ? true : false;
                $subUsers->phone = $subRec['CELLPHONE'];
                $subUsers->hp = $userRec['WTAXNUM'];
                $subUsers->payCode = $userRec['PAYCODE'];
                $subUsers->payDes = $userRec['PAYDES'];
                $subUsers->maxCredit = $userRec['MAX_CREDIT'];
                $subUsers->maxObligo = $userRec['MAX_OBLIGO'];
                $subUsers->taxCode = $userRec['TAXCODE'];
                $subUsers->agentCode = $userRec['AGENTCODE'];
                $userDto->subUsers[] = $subUsers;

            }
            $usersDto->users[] = $userDto;
        }

        return $usersDto;

    }

    public function GetUsersInfo(): UsersDto
    {
        $response = $this->GetRequest('/CUSTOMERS');
        $usersDto = new UsersDto();

        foreach ($response as $userRec) {
            if ($userRec['INACTIVEFLAG'] === null) {
                $userDto = new UserDto();
                $userDto->userExId = $userRec['CUSTNAME'];
                $userDto->userDescription = $userRec['CUSTDES'];
                $userDto->isBlocked = $userRec['INACTIVEFLAG'] === 'Y' ? true : false;
                $userDto->phone = $userRec['PHONE'];
                $userDto->hp = $userRec['WTAXNUM'];
                $userDto->payCode = $userRec['PAYCODE'];
                $userDto->payDes = $userRec['PAYDES'];
                $userDto->maxCredit = $userRec['MAX_CREDIT'];
                $userDto->maxObligo = $userRec['MAX_OBLIGO'];
                $userDto->taxCode = $userRec['TAXCODE'];
                $usersDto->users[] = $userDto;
            }
        }

        return $usersDto;
    }

    public function GetSubUsers(): UsersDto
    {
        $response = $this->GetRequest('/CUSTOMERS?$expand=CUSTPERSONNEL_SUBFORM,CUSTDISCOUNT_SUBFORM,CUSTPLIST_SUBFORM');
        $usersDto = new UsersDto();

        foreach ($response as $userRec) {
            if ($userRec['INACTIVEFLAG'] === null) {
                $globalDiscount = null;
                $priceList = null;
                foreach ($userRec['CUSTDISCOUNT_SUBFORM'] as $discountRec) {
                    $expiryDate = new \DateTime($discountRec['EXPIRYDATE']);
                    $currentDate = new \DateTime();
                    if ($expiryDate > $currentDate) {
                        $globalDiscount = $discountRec['DISCOUNT'];
                    }
                }

                foreach ($userRec['CUSTPLIST_SUBFORM'] as $priceRec) {
                    $expiryDate = new \DateTime($priceRec['PLDATE']);
                    $currentDate = new \DateTime();
                    if ($expiryDate > $currentDate) {
                        $priceList = $priceRec['PLNAME'];
                    }
                }

                foreach ($userRec['CUSTPERSONNEL_SUBFORM'] as $subUsersRec) {
                    $userDto = new UserDto();
                    $userDto->userExId = $userRec['CUSTNAME'];
                    $userDto->userDescription = $userRec['CUSTDES'];
                    $userDto->name = $subUsersRec['NAME'];
                    $userDto->telephone = $subUsersRec['PHONENUM'];
                    $userDto->maxObligo = $userRec['MAX_OBLIGO'];
                    $userDto->maxCredit = $userRec['MAX_CREDIT'];
                    $userDto->phone = $subUsersRec['CELLPHONE'];
                    $userDto->address = $userRec['ADDRESS'];
                    $userDto->town = $userRec['STATE'];
                    $userDto->isBlocked = $userRec['INACTIVEFLAG'] === 'Y' ? false : true;
                    $userDto->globalDiscount = $globalDiscount;
                    $userDto->priceList = $priceList;
                    $usersDto->users[] = $userDto;

                }
            }
        }

        return $usersDto;
    }

    public function GetMigvan(): MigvansDto
    {
        $endpoint = "/CUSTOMERS";
        $queryParameters = [
            '$select' => 'CUSTNAME',
            '$expand' => 'CUSTPART_SUBFORM($select=PARTNAME)',
        ];
        $queryString = http_build_query($queryParameters);
        $urlQuery = $endpoint . '?' . $queryString;

        $result = new MigvansDto();
        $response = $this->GetRequest($urlQuery);
        foreach ($response as $itemRec) {
            foreach ($itemRec['CUSTPART_SUBFORM'] as $subItem) {
                $obj = new MigvanDto();
                $obj->sku = $subItem['PARTNAME'];
                $obj->userExId = $itemRec['CUSTNAME'];
                $result->migvans[] = $obj;
            }
        }

        return $result;
    }

    public function GetPriceList(): PriceListsDto
    {
        $endpoint = "/PRICELIST";

        $response = $this->GetRequest($endpoint);

        $dto = new PriceListsDto();
        foreach ($response as $itemRec) {
            $obj = new PriceListDto();

            $obj->priceListExtId = $itemRec['PLNAME'];
            $obj->priceListTitle = $itemRec['PLDES'];
            $obj->priceListExperationDate = $itemRec['PLDATE'];
            $dto->priceLists[] = $obj;
        }

        return $dto;

    }

    public function GetPriceListUser(): PriceListsUserDto
    {
        $endpoint = "/CUSTOMERS";
        $queryExtras = [
            '$expand' => "CUSTPLIST_SUBFORM"
        ];
        $queryString = http_build_query($queryExtras);
        $urlQuery = $endpoint . '?' . $queryString;
        $response = $this->GetRequest($urlQuery);
        $dto = new PriceListsUserDto();

        foreach ($response as $itemRec) {
            foreach ($itemRec['CUSTPLIST_SUBFORM'] as $subRec) {
                if ($itemRec['CUSTNAME']) {
                    $userDto = new PriceListUserDto();
                    $userDto->userExId = $itemRec['CUSTNAME'];
                    $userDto->priceListExId = $subRec['PLNAME'];
                    $dto->priceLists[] = $userDto;
                }
            }
        }

        return $dto;
    }

    public function GetPrices(): PricesDto
    {

    }

    public function GetMigvansOnline(?array $skus): MigvansDto
    {
        // TODO: Implement GetMigvansOnline() method.
    }

    public function GetStocks(): StocksDto
    {

        $endpoint = "/LOGPART";
        $queryExtras = [
            '$expand' => "LOGCOUNTERS_SUBFORM"
        ];
        $queryString = http_build_query($queryExtras);
        $urlQuery = $endpoint . '?' . $queryString;

        $response = $this->GetRequest($urlQuery);
        $result = new StocksDto();
        foreach ($response as $itemRec) {
            foreach ($itemRec['LOGCOUNTERS_SUBFORM'] as $subRec) {
                $obj = new StockDto();
                $obj->sku = $itemRec['PARTNAME'];
                $obj->stock = $subRec['BALANCE'];
                $result->stocks[] = $obj;
            }
        }
        return $result;
    }

    public function GetCategories(): CategoriesDto
    {
        $data = $this->GetRequest('/FAMILYTYPES');
        $categoryResult = new CategoriesDto();

        foreach ($data as $itemRec) {
            if ($itemRec['FTNAME']) {
                $obj = new CategoryDto();
                $obj->categoryId = $itemRec['FTCODE'];
                $obj->categoryName = $itemRec['FTNAME'];
                $categoryResult->categories[] = $obj;
            }
        }

        return $categoryResult;
    }

    public function GetPriceListDetailed(): PriceListsDetailedDto
    {

        $urlQuery = '/PRICELIST?$expand=PARTPRICE2_SUBFORM($select=PARTNAME,PRICE,PERCENT)';
        $response = $this->GetRequest($urlQuery);
        $dto = new PriceListsDetailedDto();
        foreach ($response as $itemRec) {
            foreach ($itemRec['PARTPRICE2_SUBFORM'] as $subRec) {
                $obj = new PriceListDetailedDto();
                $obj->sku = $subRec['PARTNAME'];
                $obj->price = $subRec['PRICE'];
                $obj->priceList = $itemRec['PLNAME'];
                $obj->discount = $subRec['PERCENT'];
                $dto->priceListsDetailed[] = $obj;
            }
        }

        return $dto;
    }

    private function ImplodeQueryByMakats(array $makats)
    {
        $filterParts = [];
        foreach ($makats as $sku) {
            $filterParts[] = "PARTNAME eq '$sku'";
        }

        $filterString = implode(' or ', $filterParts);
        return $filterString;
    }

    private function ImplodeQueryByPlname(array $priceList)
    {
        $filterParts = [];
        foreach ($priceList as $pricePlname) {
            $filterParts[] = "PLNAME eq '$pricePlname'";
        }

        $filterString = implode(' or ', $filterParts);
        return $filterString;
    }

    public function GetPackMain(): PacksMainDto
    {
        $endpoint = "/PARTPARAM";
        $queryExtras = [
            '$expand' => "PARTPACK_SUBFORM"
        ];
        $queryString = http_build_query($queryExtras);
        $urlQuery = $endpoint . '?' . $queryString;

        $response = $this->GetRequest($urlQuery);
        $result = new PacksMainDto();
        foreach ($response as $itemRec) {
            foreach ($itemRec['PARTPACK_SUBFORM'] as $subRec) {
                $obj = new PackMainDto();
                $obj->name = $subRec['PACKNAME'];
                $obj->extId = $subRec['PACKCODE'];
                $obj->barcode = $subRec['BARCODE'];
                $obj->quantity = $subRec['PACKQUANT'];
                $result->packs[] = $obj;
            }
        }
        return $result;
    }

    public function GetPackProducts(): PacksProductDto
    {
        $endpoint = "/PARTPARAM";
        $queryExtras = [
            '$expand' => "PARTPACK_SUBFORM"
        ];
        $queryString = http_build_query($queryExtras);
        $urlQuery = $endpoint . '?' . $queryString;

        $response = $this->GetRequest($urlQuery);
        $result = new PacksProductDto();
        foreach ($response as $itemRec) {
            foreach ($itemRec['PARTPACK_SUBFORM'] as $subRec) {
                $obj = new PackProductDto();
                $obj->sku = $itemRec['PARTNAME'];
                $obj->packExtId = $subRec['PACKCODE'];
                $obj->quantity = $subRec['PACKQUANT'];
                $result->packs[] = $obj;
            }
        }
        return $result;
    }

    public function GetBonuses(): BonusesDto
    {
        $endpoint = "/CUSTOMERS";
        $queryExtras = [
            '$select' => 'CUSTNAME',
            '$expand' => "CUSTFAMILYBONUSES_SUBFORM,CUSTBONUSES_SUBFORM"
        ];
        // TODO CUSTBONUSES_SUBFORM
        $queryString = http_build_query($queryExtras);
        $urlQuery = $endpoint . '?' . $queryString;

        $response = $this->GetRequest($urlQuery);
        $result = new BonusesDto();
        foreach ($response as $itemRec) {
            foreach ($itemRec['CUSTFAMILYBONUSES_SUBFORM'] as $subRec) {

                $endpointProd = "/LOGPART";
                $family = $subRec['FAMILYNAME'];
                $queryExtrasProd = [
                    '$filter' => "FAMILYNAME eq '$family'",
                    '$select' => 'PARTNAME',
                ];
                // TODO CUSTBONUSES_SUBFORM
                $queryStringProd = http_build_query($queryExtrasProd);
                $urlQueryProd = $endpointProd . '?' . $queryStringProd;
                $responseProds = $this->GetRequest($urlQueryProd);
                foreach ($responseProds as $prodRec) {
                    $obj = new BonuseItemDto();
                    $obj->sku = $prodRec['PARTNAME'];
                    $obj->userExtId = $itemRec['CUSTNAME'];
                    $obj->bonusSku = $subRec['BONUSPARTNAME'];
                    $obj->bonusQuantity = $subRec['BONUSQUANT'];
                    $obj->minimumQuantity = $subRec['MINQUANT'];
                    $obj->title = $subRec['FAMILYDES'];
                    $obj->fromDate = $subRec['FROMDATE'];
                    $obj->expiredAt = $subRec['TODATE'];
                    $obj->extId = $subRec['FAMILYNAME'];
                    $result->bonuses[] = $obj;
                }
            }
        }

        return $result;
    }

    public function GetProductImage(string $sku)
    {
        $endpoint = "/LOGPART";
        $queryExtras = [
            '$select' => "PARTNAME,EXTFILENAME",
            '$filter' => "PARTNAME eq '$sku'"
        ];

        $queryString = http_build_query($queryExtras);
        $urlQuery = $endpoint . '?' . $queryString;
        $response = $this->GetRequest($urlQuery);
        try {
            if (!empty($response) && isset($response[0]) && isset($response[0]['EXTFILENAME'])) {
                $object = new \stdClass();
                $object->sku = $response[0]['PARTNAME'];
                $object->image = $response[0]['EXTFILENAME'];
                return $object;
            } else {
                $object = new \stdClass();
                $object->sku = '';
                $object->image = '';
                return $object;
            }
        } catch (\Exception $e) {
            $object = new \stdClass();
            $object->sku = '';
            $object->image = '';
            return $object;
        }


    }

    public function GetAgents(): UsersDto
    {
        $response = $this->GetRequest('/AGENTS');
        $usersDto = new UsersDto();

        foreach ($response as $userRec) {
            $user = new UserDto();
            $user->userExId = $userRec['AGENTCODE'];
            $user->name = $userRec['AGENTNAME'];
            $user->phone = $userRec['CELLPHONE'];
            $usersDto->users[] = $user;
        }

        return $usersDto;
    }


    public function GetAgentStatistic(string $agentId, string $dateFrom, string $dateTo): AgentStatisticDto
    {
        $endpoint = "/ORDERS";
        $currentYear = new \DateTimeImmutable();
        if ($dateFrom) {
            $dateFrom = \DateTimeImmutable::createFromFormat('Y-m-d\TH:i:s.u\Z', $dateFrom) ?: new \DateTimeImmutable($dateFrom);
        } else {
            $dateFrom = $currentYear->setDate($currentYear->format('Y'), 1, 1)->setTime(0, 0, 0, 0);
        }

        if ($dateTo) {
            $dateTo = \DateTimeImmutable::createFromFormat('Y-m-d\TH:i:s.u\Z', $dateTo) ?: new \DateTimeImmutable($dateTo);
        } else {
            $dateTo = $currentYear->setDate($currentYear->format('Y'), 12, 31)->setTime(23, 59, 59, 999999);
        }
        $dateFromFormatted = $dateFrom->format('Y-m-d\TH:i:s.u\Z');
        $dateToFormatted = $dateTo->format('Y-m-d\TH:i:s.u\Z');

        $queryParameters = [
            '$filter' => "AGENTCODE eq '$agentId' and CURDATE ge $dateFromFormatted and CURDATE le $dateToFormatted",
            '$select' => "QPRICE,CURDATE"
        ];
        $queryString = http_build_query($queryParameters);
        $urlQuery = $endpoint . '?' . $queryString;
        $response = $this->GetRequest($urlQuery);
        $currentDay = (new \DateTime())->format('Y-m-d');
        $currentMonth = (new \DateTime())->format('Y-m');

        $result = new AgentStatisticDto();
        $result->total = 0;
        $result->totalOrders = 0;
        $result->averageTotalBasket = 0;
        $result->totalPriceToday = 0;
        $result->totalPriceMonth = 0;
        $result->totalOrdersToday = 0;
        $result->totalOrdersMonth = 0;
        $totalBaskets = 0;
        $monthlyTotals = [];

// Initialize the monthlyTotals array with all months set to 0
        for ($month = 1; $month <= 12; $month++) {
            $key = $currentYear->format('Y') . '-' . str_pad($month, 2, '0', STR_PAD_LEFT);
            $monthlyTotals[$key] = 0;
        }

        foreach ($response as $item) {
            $result->total += (float)$item['QPRICE'];
            $totalBaskets++;
            $result->totalOrders++;
            $date = new \DateTime($item['CURDATE']);
            $month = $date->format('n');
            $year = $date->format('Y');

            $key = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT);

            $monthlyTotals[$key] += (float)$item['QPRICE'];

            if (substr($item['CURDATE'], 0, 10) === $currentDay) {
                $result->totalPriceToday += (float)$item['QPRICE'];
                $result->totalOrdersToday++;
            }

            if (substr($item['CURDATE'], 0, 7) === $currentMonth) {
                $result->totalPriceMonth += (float)$item['QPRICE'];
                $result->totalOrdersMonth++;
            }
        }
        $result->averageTotalBasket = $totalBaskets > 0 ? $result->total / $totalBaskets : 0;
        $hebrewMonths = [
            1 => 'ינואר',  // January
            2 => 'פברואר', // February
            3 => 'מרץ',    // March
            4 => 'אפריל',  // April
            5 => 'מאי',    // May
            6 => 'יוני',   // June
            7 => 'יולי',   // July
            8 => 'אוגוסט', // August
            9 => 'ספטמבר', // September
            10 => 'אוקטובר', // October
            11 => 'נובמבר', // November
            12 => 'דצמבר'  // December
        ];
        $monthlyTotalsArray = [];
        foreach ($monthlyTotals as $key => $total) {
            list($year, $month) = explode('-', $key);
            $monthlyTotalsArray[] = [
                'month' => (int)$month,
                'total' => round($total, 2),
                'monthTitle' => $hebrewMonths[(int)$month]
            ];
        }
        $result->monthlyTotals = $monthlyTotalsArray;

        return $result;
    }


    public function SalesKeeperAlert(string $userExtId): SalesKeeperAlertDto
    {
        $endpoint = "/AINVOICES";
        $queryExtras = [
            '$filter' => "CUSTNAME eq '$userExtId'",
            '$select' => "TOTPRICE,IVDATE",
        ];
        $queryString = http_build_query($queryExtras);
        $urlQuery = $endpoint . '?' . $queryString;
        $response = $this->GetRequest($urlQuery);

        $endpoint2 = "/CINVOICES";
        $queryExtras2 = [
            '$filter' => "CUSTNAME eq '$userExtId'",
            '$select' => "TOTPRICE,IVDATE",
        ];
        $queryString2 = http_build_query($queryExtras2);
        $urlQuery2 = $endpoint2 . '?' . $queryString2;
        $response2 = $this->GetRequest($urlQuery2);

        // Combine responses
        $data = array_merge($response, $response2);

        // Parse dates and filter data
        $currentYear = (int)date('Y');
        $currentMonth = (int)date('m');
        $previousMonth = $currentMonth - 1 > 0 ? $currentMonth - 1 : 12;
        $previousMonthYear = $currentMonth - 1 > 0 ? $currentYear : $currentYear - 1;
        $previousYear = $currentYear - 1;

        // Initialize variables
        $sumCurrentPrevMonth = 0;
        $sumPreviousYearPrevMonth = 0;
        $lastThreeMonthsPrices = [];

        foreach ($data as $item) {
            $date = new \DateTime($item['IVDATE']);
            $year = (int)$date->format('Y');
            $month = (int)$date->format('m');
            $totPrice = (float)$item['TOTPRICE'];

            // Sum for previous month of current year
            if ($year === $currentYear && $month === $previousMonth) {
                $sumCurrentPrevMonth += $totPrice;
            }

            // Sum for previous month of previous year
            if ($year === $previousYear && $month === $previousMonth) {
                $sumPreviousYearPrevMonth += $totPrice;
            }

            // Collect prices for the last 3 months
            $monthDiff = (($currentYear - $year) * 12) + ($currentMonth - $month);
            if ($monthDiff >= 1 && $monthDiff <= 3) {
                $lastThreeMonthsPrices[] = $totPrice;
            }
        }

        // Calculate the average of the last 3 months
        $averageLastThreeMonths = count($lastThreeMonthsPrices) > 0
            ? array_sum($lastThreeMonthsPrices) / count($lastThreeMonthsPrices)
            : 0;

        // Output results
        $kepper = new SalesKeeperAlertDto();
        $kepper->sumPreviousMonthCurrentYear = $sumCurrentPrevMonth;
        $kepper->sumPreviousMonthPreviousYear = $sumPreviousYearPrevMonth;
        $kepper->averageLastThreeMonths = $averageLastThreeMonths;
        return $kepper;
    }

    public function SalesQuantityKeeperAlert(string $userExtId): SalesQuantityKeeperAlertDto
    {
        // Endpoint 1: AINVOICES
        $endpoint = "/AINVOICES";
        $queryExtras = [
            '$filter' => "CUSTNAME eq '$userExtId'",
            '$select' => "IVDATE,IVNUM,DEBIT,IVTYPE",
            '$expand' => 'AINVOICEITEMS_SUBFORM($select=PARTNAME,TQUANT,PDES)' // Corrected to AINVOICEITEMS_SUBFORM
        ];
        $queryString = http_build_query($queryExtras);
        $urlQuery = $endpoint . '?' . $queryString;
        $response = $this->GetRequest($urlQuery);

        // Endpoint 2: CINVOICES (with CINVOICEITEMS_SUBFORM)
        $endpoint2 = "/CINVOICES";
        $queryExtras2 = [
            '$filter' => "CUSTNAME eq '$userExtId'",
            '$select' => "IVDATE,IVNUM,DEBIT,IVTYPE",
            '$expand' => 'CINVOICEITEMS_SUBFORM($select=PARTNAME,TQUANT,PDES)' // Corrected to CINVOICEITEMS_SUBFORM
        ];
        $queryString2 = http_build_query($queryExtras2);
        $urlQuery2 = $endpoint2 . '?' . $queryString2;
        $response2 = $this->GetRequest($urlQuery2);

        // Combine responses from both endpoints
        $data = array_merge($response, $response2);

        // Get current date details
        $currentYear = (int)date('Y');
        $currentMonth = (int)date('m');
        $previousMonth = $currentMonth - 1 > 0 ? $currentMonth - 1 : 12;
        $previousYear = $currentYear - 1;

        // Initialize results storage
        $results = [];

        // Process the invoices and their items
        foreach ($data as $invoice) {
            $invoiceDate = new \DateTime($invoice['IVDATE']);
            $year = (int)$invoiceDate->format('Y');
            $month = (int)$invoiceDate->format('m');

            // Process AINVOICEITEMS_SUBFORM or CINVOICEITEMS_SUBFORM depending on the endpoint
            if (isset($invoice['AINVOICEITEMS_SUBFORM'])) {
                $items = $invoice['AINVOICEITEMS_SUBFORM']; // AINVOICEITEMS_SUBFORM for AINVOICES endpoint
            } elseif (isset($invoice['CINVOICEITEMS_SUBFORM'])) {
                $items = $invoice['CINVOICEITEMS_SUBFORM']; // CINVOICEITEMS_SUBFORM for CINVOICES endpoint
            } else {
                continue; // If no subform exists, skip this invoice
            }

            foreach ($items as $item) {
                $partName = $item['PARTNAME'];
                $tQuant = (float)$item['TQUANT'];
                $productDescription = isset($item['PDES']) ? $item['PDES'] : ''; // Product description

                if (!isset($results[$partName])) {
                    $results[$partName] = [
                        'sumPreviousMonthCurrentYear' => 0,
                        'sumPreviousMonthPreviousYear' => 0,
                        'lastThreeMonths' => [],
                        'productDescription' => $productDescription, // Store the product description
                    ];
                }

                // Sum for previous month of current year
                if ($year === $currentYear && $month === $previousMonth) {
                    $results[$partName]['sumPreviousMonthCurrentYear'] += $tQuant;
                }

                // Sum for previous month of previous year
                if ($year === $previousYear && $month === $previousMonth) {
                    $results[$partName]['sumPreviousMonthPreviousYear'] += $tQuant;
                }

                // Collect quantities for the last 3 months
                $monthDiff = (($currentYear - $year) * 12) + ($currentMonth - $month);
                if ($monthDiff >= 1 && $monthDiff <= 3) {
                    $results[$partName]['lastThreeMonths'][] = $tQuant;
                }
            }
        }

        $result = new SalesQuantityKeeperAlertDto();
        foreach ($results as $partName => $result) {
            $averageLastThreeMonths = count($result['lastThreeMonths']) > 0
                ? array_sum($result['lastThreeMonths']) / count($result['lastThreeMonths'])
                : 0;

            // Prepare final output with product description
            $obj = new SalesQuantityKeeperAlertLineDto();
            $obj->sku = $partName;
            $obj->productDescription = $result['productDescription'];
            $obj->averageLastThreeMonths = $averageLastThreeMonths;
            $obj->sumPreviousMonthPreviousYear = $result['sumPreviousMonthPreviousYear'];
            $obj->sumPreviousMonthCurrentYear = $result['sumPreviousMonthCurrentYear'];
            $result->lines[] = $obj;
        }

        return $result;
    }

    public function UserMoneyInfo(string $userExtId): MoneyInfo
    {
        $res = new MoneyInfo();
        $endpoint = "/CUSTOMERS";
        $queryExtras = [
            '$filter' => "CUSTNAME eq '$userExtId'",
            '$expand' => "CUSTOBLIGO_SUBFORM"
        ];
        $queryString = http_build_query($queryExtras);
        $urlQuery = $endpoint . '?' . $queryString;
        $response = $this->GetRequest($urlQuery);
        return $res;
    }

    public function GetPriceOnline(string $userExtId, string $sku, array|string $priceListNumber): PriceOnlineDto
    {
        // TODO: Implement GetPriceOnline() method.
    }

    public function GetStockOnline(string $sku, string $warehouse): StockDto
    {
        // TODO: Implement GetStockOnline() method.
    }

    public function ProductsImBuy(string $userExtId): array
    {
        $endpoint = "/ORDERS";
        $queryParameters = [
            '$filter' => "CUSTNAME eq '$userExtId'",
            '$expand' => 'ORDERITEMS_SUBFORM($select=PARTNAME)',
            '$top' => '20'
        ];

        $queryString = http_build_query($queryParameters);
        $urlQuery = $endpoint . '?' . $queryString;
        $response = $this->GetRequest($urlQuery);
        $result = [];
        foreach ($response as $itemRec) {
            foreach ($itemRec['ORDERITEMS_SUBFORM'] as $subItem) {
                $result[] = $subItem['PARTNAME'];
            }
        }

        $result = array_unique($result);

        $result = array_values($result);

        return $result;
    }

    public function ProductsImNotBuy(string $userExtId): array
    {
        // TODO: Implement
    }

}