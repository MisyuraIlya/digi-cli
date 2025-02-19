<?php

namespace App\Erp\Core\SAP;

use App\Entity\History;
use App\Entity\User;
use App\Enum\DocumentsTypeSap;
use App\Enum\HistoryDocumentTypeEnum;
use App\Enum\UsersTypes;
use App\Erp\Core\Dto\AgentStatisticDto;
use App\Erp\Core\Dto\BonusesDto;
use App\Erp\Core\Dto\CartessetDto;
use App\Erp\Core\Dto\CartessetLineDto;
use App\Erp\Core\Dto\CategoriesDto;
use App\Erp\Core\Dto\CategoryDto;
use App\Erp\Core\Dto\DocumentDto;
use App\Erp\Core\Dto\DocumentItemDto;
use App\Erp\Core\Dto\DocumentItemFileDto;
use App\Erp\Core\Dto\DocumentItemsDto;
use App\Erp\Core\Dto\DocumentsDto;
use App\Erp\Core\Dto\HovotDto;
use App\Erp\Core\Dto\MigvansDto;
use App\Erp\Core\Dto\MoneyInfo;
use App\Erp\Core\Dto\MoneyInfoLine;
use App\Erp\Core\Dto\PacksMainDto;
use App\Erp\Core\Dto\PacksProductDto;
use App\Erp\Core\Dto\PriceListDto;
use App\Erp\Core\Dto\PriceListsDetailedDto;
use App\Erp\Core\Dto\PriceListsDto;
use App\Erp\Core\Dto\PriceListsUserDto;
use App\Erp\Core\Dto\PriceListUserDto;
use App\Erp\Core\Dto\PriceOnlineDto;
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
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Sap implements ErpInterface
{
    private string $username;
    private string $password;

    private string $erpDb;
    private string $url;

    private ?string $sessionId = null;
    private ?string $routeId = null;


    public function __construct(
        string $url,
        string $username,
        string $password,
        HttpClientInterface $httpClient,
        ?string $erpDb
    )
    {
        $this->username = $username;
        $this->password = $password;
        $this->url = $url;
        $this->httpClient = $httpClient;
        $this->erpDb = $erpDb;
        $this->CategoryTable = $_ENV['CATEGORY_STATE'] ;
        $this->ErpCategoryLvl1 = $_ENV['CATEGORY_LVL_1'] ;
        $this->ErpCategoryLvl2 = $_ENV['CATEGORY_LVL_2'] ;
        $this->ErpCategoryLvl3 = $_ENV['CATEGORY_LVL_3'] ;
    }


    /**
     * Fetches the token and sets sessionId and routeId for authorization.
     *
     * @throws \Exception If the request fails or credentials are invalid.
     */
    private function fetchToken(): void
    {
        $loginUrl = $this->url . '/Login';

        try {
            $response = $this->httpClient->request('POST', $loginUrl, [
                'json' => [
                    'CompanyDB' => $this->erpDb,
                    'UserName' => $this->username,
                    'Password' => $this->password,
                ],
                'verify_peer' => false,
                'verify_host' => false,
            ]);
            if (200 !== $response->getStatusCode()) {
                throw new \Exception('Unable to fetch token. Status code: ' . $response->getStatusCode());
            }

            $data = $response->toArray();

            $this->sessionId = $data['SessionId'];
            // Assuming ROUTEID is part of the cookie header in the response
            $cookies = $response->getHeaders()['set-cookie'] ?? [];
            foreach ($cookies as $cookie) {
                if (strpos($cookie, 'ROUTEID=') !== false) {
                    $this->routeId = explode('=', explode(';', $cookie)[0])[1];
                }
            }

            if (!$this->sessionId || !$this->routeId) {
                throw new \Exception('Failed to extract session or route information.');
            }
        } catch (\Exception $e) {
            throw new \Exception('Error fetching token: ' . $e->getMessage());
        }
    }

    /**
     * Make an authorized request to the API.
     *
     * @param string $method HTTP method (GET, POST, etc.)
     * @param string $endpoint API endpoint (relative to base URL)
     * @param array|null $body Request body for POST/PATCH requests
     * @return array Response data as an associative array
     * @throws \Exception If the request fails
     */
    private function makeAuthorizedRequest(string $method, string $endpoint, ?array $body = null): array
    {
        if (!$this->sessionId) {
            $this->fetchToken();
        }

        $headers = [
            'Cookie' => "B1SESSION={$this->sessionId}; ROUTEID={$this->routeId}",
        ];

        try {
            $response = $this->httpClient->request($method, $this->url . $endpoint, [
                'headers' => $headers,
                'json' => $body,
                'verify_peer' => false,
                'verify_host' => false,
            ]);

            if ($response->getStatusCode() === 401) {
                // Handle unauthorized error: re-fetch token and retry
                $this->fetchToken();
                return $this->makeAuthorizedRequest($method, $endpoint, $body);
            }

            return $response->toArray();
        } catch (\Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface $e) {
            // Capture the response body
            $response = $e->getResponse();
            $responseContent = $response->getContent(false); // 'false' avoids re-throwing the exception

            throw new \Exception('Request failed: ' . $e->getMessage() . ' Response body: ' . $responseContent);
        } catch (\Exception $e) {
            throw new \Exception('Request failed: ' . $e->getMessage());
        }
    }

    public function GetRequest(?string $query)
    {
        if (!$query) {
            throw new \InvalidArgumentException('Query cannot be null.');
        }

        return $this->makeAuthorizedRequest('GET', $query);
    }

    public function PatchRequest(object $object, string $table)
    {
        return $this->makeAuthorizedRequest('PATCH', $table, (array)$object);
    }

    public function PostRequest(\stdClass $object, string $table)
    {
        return $this->makeAuthorizedRequest('POST', $table, (array)$object);
    }
    public function SendOrder(History $history): ?string
    {
        $response = null;
        if($history->getDocumentType() == HistoryDocumentTypeEnum::ORDER) {
            $obj = $this->SendTemplate($history,6);
            $response = $this->PostRequest($obj, '/Orders');
            if(isset($response['DocNum'])) {
                return $response['DocNum'];
            } else {
                throw new \Exception('הזמנה לא שודרה');
            }
        } elseif ($history->getDocumentType() == HistoryDocumentTypeEnum::QUOATE) {
            $obj = $this->SendTemplate($history,12);
            $response = $this->PostRequest($obj, '/Quotations');
            if(isset($response['DocNum'])) {
                return $response['DocNum'];
            } else {
                throw new \Exception('הצעת מחיר לא שודרה');
            }
        } elseif ($history->getDocumentType() == HistoryDocumentTypeEnum::RETURN) {
            $obj = $this->SendTemplate($history,5,'110');
            $response = $this->PostRequest($obj, '/Returns');
            if(isset($response['DocNum'])) {
                return $response['DocNum'];
            } else {
                throw new \Exception('שחזור הזמנה לא שודרה');
            }
        } else {
            throw new \Exception('לא נמצא מסמך כזה');
        }

        return $response;
    }

    private function SendTemplate(History $history, int $numer, ?string $warehouse = null)
    {
        $obj = new \stdClass();
        $obj->CardCode = $history->getUser()->getExtId();
        $obj->Series = $numer;
        $obj->DocDate = new \DateTimeImmutable();
        $obj->DocDueDate = $history->getDeliveryDate()->format('Y-m-d\TH:i:sP') ?? $history->getCreatedAt()->format('Y-m-d\TH:i:sP');
        $obj->Comments = $history->getOrderComment();
        $obj->DocTotal = $history->getTotal();
        $obj->Address = $history->getCity() . ' - ' . $history->getAddress();
        $obj->DocumentLines = [];

        $obj->AddressExtension = new \stdClass();
        $obj->AddressExtension->ShipToCity = $history->getCity();
        $obj->AddressExtension->ShipToStreet = $history->getAddress();
        foreach ($history->getHistoryDetaileds() as $itemRec){
            assert($itemRec instanceof HistoryDetailed);
            $objLine = new \stdClass();
            $objLine->ItemCode = $itemRec->getProduct()->getSku();
            $objLine->Quantity = $itemRec->getQuantity();
            $objLine->UnitPrice = $itemRec->getSinglePrice();
            $objLine->BarCode = $itemRec->getProduct()->getBarcode();
            if(!empty($history->getAgent())){
                $objLine->SalesPersonCode = $history->getAgent()->getExtId();
            }
            if($warehouse){
                $objLine->WarehouseCode = $warehouse;
            }
            $objLine->UseBaseUnits = 'tYES';
            $obj->DocumentLines[] = $objLine;
        }
        return $obj;
    }

    public function GetDocuments(
        \DateTimeImmutable $dateFrom,
        \DateTimeImmutable $dateTo,
        string $documentsType,
        int $pageSize,
        int $currentPage,
        ?User $user,
        ?string $search = null
    ): DocumentsDto
    {
        $endpoint = '/'.$documentsType;
        $dateFrom = $dateFrom->format('Y-m-d\TH:i:s.u\Z');
        $dateTo = $dateTo->format('Y-m-d\TH:i:s.u\Z');
        $skip = ($currentPage - 1) * $pageSize;
        $filter = "DocDate ge $dateFrom and DocDate le $dateTo";
        if ($user) {
            $userExtId = $user->getExtId();
            if ($user->getRole() === UsersTypes::AGENT || $user->getRole() === UsersTypes::SUPER_AGENT) {
                $filter .= " and AgentCode eq '$userExtId'";
            } else {
                $filter .= " and CardCode eq '$userExtId'";
            }
        }
        if (!empty($search)) {
            $searchFilter = "(contains(DocNum, '$search') or contains(CardCode, '$search') or contains(CardName, '$search'))";
            $filter .= " and $searchFilter";
        }

        $queryParameters = [
            '$filter' => $filter,
            '$select' => "CardCode,CardName,DocTotal,DocDate,DocumentStatus,DocNum,UpdateDate,AgentCode",
            '$top' => $pageSize,
            '$skip' => $skip,
            '$inlinecount' => 'allpages'
        ];

        $queryString = http_build_query($queryParameters);
        $urlQuery = $endpoint . '?' . $queryString;

        $response = $this->GetRequest($urlQuery);
        $totalRecords = $response['@odata.count'] ?? 0;
        $totalPages = ceil($totalRecords / $pageSize);
        $result = [];
        foreach ($response['value'] as $itemRec) {
            $dto = new DocumentDto();
            $dto->userName = $itemRec['CardName'];
            $dto->userExId = $itemRec['CardCode'];
            $dto->agentExId = $itemRec['AgentCode']; //TODO
            $dto->agentName = ''; //TODO
            $dto->total = $itemRec['DocTotal'];
            $dto->createdAt = $itemRec['DocDate'];
            $dto->documentType = $documentsType;
            $dto->status = $itemRec['DocumentStatus'];
            $dto->documentNumber = $itemRec['DocNum'];
            $dto->updatedAt = $itemRec['UpdateDate'];
            $result[] = $dto;
        }

        $obj = new DocumentsDto();
        $obj->documents = $result;
        $obj->totalRecords =$totalRecords;
        $obj->totalPages = $totalPages;
        $obj->currentPage = $currentPage;
        $obj->pageSize = $pageSize;
//        dd($obj);

        return $obj;
    }

    public function GetDocumentsItem(string $documentNumber, string $documentType): DocumentItemsDto
    {
        $endpoint = "/".$documentType;
        $queryParameters = [
            '$filter' => "DocNum eq $documentNumber",
            '$select' => "DocTotal,VatSum,DiscountPercent,DocumentLines"
        ];
        $queryString = http_build_query($queryParameters);
        $urlQuery = $endpoint . '?' . $queryString;
        $response = $this->GetRequest($urlQuery);
        $result = new DocumentItemsDto();
        foreach ($response['value']  as $itemRec) {
            $result->totalAfterDiscount = $itemRec['DocTotal'];
            $result->totalPrecent = $itemRec['DiscountPercent'];
            $result->totalPriceAfterTax = $itemRec['DocTotal'];
            $result->totalTax = $itemRec['VatSum'];
            $result->documentType = $documentType;
            foreach ($itemRec['DocumentLines'] as $subItem) {
                $dto = new DocumentItemDto();
                $dto->sku = $subItem['ItemCode'];
                $dto->quantity = $subItem['Quantity'];
                $dto->title = $subItem['ItemDescription'];
                $dto->priceByOne = $subItem['Price'];
                $dto->total = $subItem['LineTotal'];
                $dto->discount = $subItem['DiscountPercent'];
                $result->products[] = $dto;
            }
        }
        return $result;
    }

    public function GetCartesset(string $userExId, \DateTimeImmutable $dateFrom, \DateTimeImmutable $dateTo): CartessetDto
    {
        $endpoint = "/JournalEntries";
        $dateFrom = $dateFrom->format('Y-m-d\TH:i:s.u\Z');
        $dateTo = $dateTo->format('Y-m-d\TH:i:s.u\Z');
        $queryParameters = [
            '$filter' => "Reference eq '$userExId' and ReferenceDate ge $dateFrom and ReferenceDate le $dateTo",
            '$select' => "JournalEntryLines"
        ];
        $queryString = http_build_query($queryParameters);
        $urlQuery = $endpoint . '?' . $queryString;
        $response = $this->GetRequest($urlQuery);
        $result = new CartessetDto();
        foreach ($response['value'] as $itemRec) {
            foreach ($itemRec['JournalEntryLines'] as $subItem) {
                $dto = new CartessetLineDto();
                $dto->createdAt = $subItem['DueDate'];
                $dto->tnua =  $subItem['AccountCode'];
                $dto->asmahta1 =  $subItem['ShortName'];
                $dto->dateEreh =  $subItem['ReferenceDate1'];
                $dto->description =  $subItem['LineMemo'];
                $dto->hova =  $subItem['Credit'];
                $dto->zhut =  $subItem['Debit'];
                $dto->yetra =  $subItem['DebitSys'];
                $result->lines[] = $dto;
            }
        }
        return $result;
    }

    public function GetHovot(string $userExId, \DateTimeImmutable $dateFrom, \DateTimeImmutable $dateTo): HovotDto
    {
        // TODO: Implement ONLINE
    }

    public function PurchaseHistoryByUserAndSku(string $userExtId, string $sku): PurchaseHistory
    {
        $endpoint = "/SQLQueries('GetInvoicesByUserAndSku')/List";
        $queryParameters = [
            'itemCode' => "'$sku'",
            'cardCode' => "'$userExtId'"
        ];
        $queryString = http_build_query($queryParameters);
        $urlQuery = $endpoint . '?' . $queryString;
        $response = $this->GetRequest($urlQuery);
        $result = new PurchaseHistory();
        foreach ($response['value'] as $itemRec) {
            $obj = new PurchaseHistoryItem();
            $obj->documentNumber = $itemRec['DocNum'];
            $obj->date = \DateTime::createFromFormat('Ymd', $itemRec['DocDate'])->format('Y-m-d');
            $obj->quantity = $itemRec['Quantity'];
            $obj->price = $itemRec['Price'];
            $obj->vatPrice = $itemRec['PriceAfVAT'] - $itemRec['Price'];
            $obj->discount = $itemRec['DiscPrcnt'];
            $obj->totalPrice = $itemRec['PriceAfVAT'];
            $obj->vatTotal = $itemRec['PriceAfVAT'];
            $result->items[] = $obj;
        }
        return $result;
    }

    public function GetAgentStatistic(string $agentId, string $dateFrom, string $dateTo): AgentStatisticDto
    {
        $endpoint = "/SQLQueries('getAgentStatistic')/List";
        $date = date('Y-m-d');
        $queryParameters = [
            'currentDate' => "'$date'",
            'dateFrom' => "'$dateFrom'",
            'dateTo' => "'$dateTo'",
            'agentCode' => "$agentId",

        ];
        $queryString = http_build_query($queryParameters);
        $urlQuery = $endpoint . '?' . $queryString;
        $response = $this->GetRequest($urlQuery);
        $obj = new AgentStatisticDto();
        $obj->averageTotalBasketChoosedDates = $response['value'][0]['AverageTotalBasketChoosedDates'] ?? 0;
        $obj->averageTotalBasketMonth = $response['value'][0]['AverageTotalBasketMonth'] ?? 0;
        $obj->averageTotalBasketToday = $response['value'][0]['AverageTotalBasketToday'] ?? 0;

        $obj->totalInvoicesChoosedDates = $response['value'][0]['totalInvoicesChoosedDates'] ?? 0;
        $obj->totalInvoicesMonth = $response['value'][0]['totalInvoicesMonth'] ?? 0;
        $obj->totalInvoicesToday = $response['value'][0]['totalInvoicesToday'] ?? 0;

        $obj->totalPriceChoosedDates = $response['value'][0]['totalPriceChoosedDates'] ?? 0;
        $obj->totalPriceMonth = $response['value'][0]['totalPriceMonth'] ?? 0;
        $obj->totalPriceToday = $response['value'][0]['totalPriceToday'] ?? 0;
        $months = $this->GetAgentStatisticByMonths($agentId,'2024');
        $obj->monthlyTotals =$months;
        return $obj;
    }

    public function GetAgentStatisticByMonths(string $agentId,string $year)
    {
        $endpoint = "/SQLQueries('getAgentStatisticYearByMonth')/List";
        $queryParameters = [
            'agentCode' => "'$agentId'",
        ];
        for ($month = 1; $month <= 12; $month++) {
            $queryParameters["month$month"] = sprintf("'%s-%02d-01'", $year, $month);
        }

        $queryString = http_build_query($queryParameters);
        $urlQuery = $endpoint . '?' . $queryString;
        $response = $this->GetRequest($urlQuery);

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

        $result = [];
        for ($month = 1; $month <= 12; $month++) {
            $result[$month] = [
                'month' => $month,
                'total' => 0,
                'monthTitle' => $hebrewMonths[$month],
            ];
        }

        foreach ($response['value'] as $itemRec) {
            $monthNumber = (int)date("n", strtotime($itemRec['YearMonth']));
            $result[$monthNumber]['total'] = round($itemRec['TotalPrice'], 2);
        }

        $result = array_values($result);

        return $result;
    }

    public function SalesKeeperAlert(string $userExtId): SalesKeeperAlertDto
    {
        $endpoint = "/SQLQueries('salesKeeper')/List";
        $dateCurrent = "'" . (new \DateTime())->modify('-2 months')->format('Y-m-d') . "'";
        $nextMonthCurrent = "'" . (new \DateTime())->modify('-1 month')->format('Y-m-d') . "'";
        $previousDateStart = "'" . (new \DateTime())->modify('-1 year -2 months')->format('Y-m-d') . "'";
        $nextMonthPreviousStart = "'" . (new \DateTime())->modify('-1 year -1 month')->format('Y-m-d') . "'";
        $threeMonthsAgoStart = "'" . (new \DateTime())->modify('-4 months')->format('Y-m-d') . "'";
        $threeMonthsAgoEnd = "'" . (new \DateTime())->modify('-1 month')->format('Y-m-d') . "'";
        $queryParameters = [
            'dateCurrent' => $dateCurrent,
            'nextMonthCurrent' => $nextMonthCurrent,
            'previousDate' => $previousDateStart,
            'nextMonthPrevious' => $nextMonthPreviousStart,
            'threeMonthsAgoStart' => $threeMonthsAgoStart,
            'threeMonthsAgoEnd' => $threeMonthsAgoEnd,
            'cardCode' => "'$userExtId'",
        ];
        $queryString = http_build_query($queryParameters);
        $urlQuery = $endpoint . '?' . $queryString;
        $response = $this->GetRequest($urlQuery);
        $result = new SalesKeeperAlertDto();
        $result->sumPreviousMonthCurrentYear = $response['value'][0]['CurrentMonthSum'] ?? 0;
        $result->sumPreviousMonthPreviousYear = $response['value'][0]['PreviousYearMonthSum'] ?? 0;
        $result->averageLastThreeMonths = $response['value'][0]['ThreeMonthAgoAvg'] ?? 0;
        return $result;
    }

    public function SalesQuantityKeeperAlert(string $userExtId): SalesQuantityKeeperAlertDto
    {
        $endpoint = "/SQLQueries('quantityKeeper')/List";
        $dateCurrent = "'" . (new \DateTime())->modify('-2 months')->format('Y-m-d') . "'";
        $nextMonthCurrent = "'" . (new \DateTime())->modify('-1 month')->format('Y-m-d') . "'";
        $previousDateStart = "'" . (new \DateTime())->modify('-1 year -2 months')->format('Y-m-d') . "'";
        $nextMonthPreviousStart = "'" . (new \DateTime())->modify('-1 year -1 month')->format('Y-m-d') . "'";
        $threeMonthsAgoStart = "'" . (new \DateTime())->modify('-4 months')->format('Y-m-d') . "'";
        $threeMonthsAgoEnd = "'" . (new \DateTime())->modify('-1 month')->format('Y-m-d') . "'";
        $queryParameters = [
            'cardCode' => "'$userExtId'",
            'dateFrom' => $dateCurrent,
            'dateTo' => $nextMonthCurrent,
            'dateYearAgoFrom' => $previousDateStart,
            'dateYearAgoTo' => $nextMonthPreviousStart,
            'dateThreeMonthsAgoFrom' => $threeMonthsAgoStart,
            'dateThreeMonthsAgoTo' => $threeMonthsAgoEnd,
        ];
        $queryString = http_build_query($queryParameters);
        $urlQuery = $endpoint . '?' . $queryString;
        $response = $this->GetRequest($urlQuery);
        $result = new SalesQuantityKeeperAlertDto();
        foreach ($response['value'] as $itemRec) {
            $obj = new SalesQuantityKeeperAlertLineDto();
            $obj->sku = $itemRec['ItemCode'];
            $obj->productDescription = $itemRec['ItemDescription'];
            $obj->sumPreviousMonthCurrentYear = $itemRec['Quantity'];
            $obj->sumPreviousMonthPreviousYear = $itemRec['YearAgoQuantity'];
            $obj->averageLastThreeMonths = $itemRec['ThreeMonthAvgQuantity'];
            $result->lines[] = $obj;
        }

        return $result;
    }

    public function GetPriceOnline(string $userExtId, string $sku, array|string $priceListNumber): PriceOnlineDto
    {
        $endpoint = "/SQLQueries('GetPricesTree')/List";
        $dateCurrent =  (new \DateTime())->format('Y-m-d');
        $queryParameters = [
            'date' => "'$dateCurrent'",
            'itemCode' => "'$sku'",
            'priceList' => "'$priceListNumber'",
            'cardCode' => "'$userExtId'"
        ];
        $queryString = http_build_query($queryParameters);
        $urlQuery = $endpoint . '?' . $queryString;
        $response = $this->GetRequest($urlQuery);
        $obj = new PriceOnlineDto();
        foreach ($response['value'] as $itemRec) {
            $obj->basePrice = $itemRec['PriceListPrice'];
            $obj->priceLvl1 = $itemRec['SpecialPriceLvl1'];
            $obj->discountLvl1 = $itemRec['SpecialDiscountLvl1'];
            $obj->priceLvl2 = $itemRec['SpecialPriceLvl2'];
            $obj->discountLvl2 = $itemRec['SpecialDiscountLvl2'];
            $obj->currency = $itemRec['Currency'];
        }
        return $obj;
    }

    public function GetStockOnline(string $sku, string $warehouse): StockDto
    {
        $endpoint = "/SQLQueries('GetStockByItemCodeAndWarehouse')/List";
        $queryParameters = [
            'ItemCode' => "'$sku'",
            'warehouse' => "'$warehouse'"
        ];
        $queryString = http_build_query($queryParameters);
        $urlQuery = $endpoint . '?' . $queryString;
        $response = $this->GetRequest($urlQuery);
        $obj = new StockDto();
        foreach ($response['value'] as $itemRec) {
            $obj->sku = $itemRec['sku'];
            $obj->stock = $itemRec['stock'];
            $obj->warehouse = $itemRec['warehouseCode'];
        }
        return $obj;
    }

    public function UserMoneyInfo(string $userExtId):MoneyInfo
    {
        $endpoint = "/BusinessPartners('$userExtId')";
        $queryParameters = [
        ];
        $queryString = http_build_query($queryParameters);
        $urlQuery = $endpoint . '?' . $queryString;
        $response = $this->GetRequest($urlQuery);

        $obj = new MoneyInfo();

        $data10 = new MoneyInfoLine();
        $data10->key =  "מס חברה";
        $data10->value = $response['FederalTaxID'];
        $obj->lines[] = $data10;

        $data1 = new MoneyInfoLine();
        $data1->key = 'תקרת אשראי';
        $data1->value = $response['CreditLimit'];
        $obj->lines[] = $data1;

        $data2 = new MoneyInfoLine();
        $data2->key = 'יתרת חשבון';
        $data2->value = $response['CurrentAccountBalance'];
        $obj->lines[] = $data2;

        $data3 = new MoneyInfoLine();
        $data3->key = 'הזמנות';
        $data3->value = $response['OpenOrdersBalance'];
        $obj->lines[] = $data3;

        $data4 = new MoneyInfoLine();
        $data4->key = 'שיקים';
        $data4->value = $response['OpenChecksBalance'];
        $obj->lines[] = $data4;

        $data5 = new MoneyInfoLine();
        $data5->key = 'תעודות משלוח';
        $data5->value = $response['OpenDeliveryNotesBalance'];
        $obj->lines[] = $data5;

        $data6 = new MoneyInfoLine();
        $data6->key = 'אובליגו';
        $data6->value = $response['U_ChainOblig'];
        $obj->lines[] = $data6;

        $data7 = new MoneyInfoLine();
        $data7->key = 'ניצול אובליגו';
        $data7->value = $response['U_ChainUse'];
        $obj->lines[] = $data7;

        $data8 = new MoneyInfoLine();
        $data8->key = "ממוצע סכום שהתקבל";
        $data8->value = $response['U_AvgRcv'];
        $obj->lines[] = $data8;

        $data9 = new MoneyInfoLine();
        $data9->key =  "ממוצע סכום ששולם";
        $data9->value = $response['U_AvgPay'];
        $obj->lines[] = $data9;

        return $obj;
    }

    public function GetCategories(): CategoriesDto
    {
        $endpoint = "/SQLQueries('GetCategories')/List";
        $queryParameters = [

        ];
        $queryString = http_build_query($queryParameters);
        $urlQuery = $endpoint . '?' . $queryString;

        $result = new CategoriesDto();
        do {
            $response = $this->GetRequest($urlQuery);
            foreach ($response['value'] as $itemRec) {
                $item = new CategoryDto();
                $item->categoryId = $itemRec['CategoryCode'];
                $item->categoryName = $itemRec['CategoryTitle'];
                $item->parentId = $itemRec['SubGroupCode'];
                $item->parentName = $itemRec['SubGroupTitle'];
                $result->categories[] = $item;
            }
            if (isset($response['@odata.nextLink'])) {
                $urlQuery = '/'.$response['@odata.nextLink'];
            } else {
                $urlQuery = null;
            }
        } while ($urlQuery);
        return $result;
    }

    public function GetProducts(?int $pageSize, ?int $skip): ProductsDto
    {
        $endpoint = "/Items";
        $queryParameters = [
            '$select' => "ItemCode,ItemName,U_IsVisibleOnWebshop,BarCode,ItemsGroupCode,U_SubGrpCod,U_SubGrpNam,U_Color,U_Class,U_Input",
            '$filter' => "U_IsVisibleOnWebshop eq 'Y'"
        ];
        $queryString = http_build_query($queryParameters);
        $urlQuery = $endpoint . '?' . $queryString;
        $result = new ProductsDto();
        do {
            $response = $this->GetRequest($urlQuery);
            foreach ($response['value'] as $itemRec) {
                $item = new ProductDto();
                $item->categoryLvl2Id = $itemRec['ItemsGroupCode'];
                $item->categoryLvl3Id = $itemRec['U_SubGrpCod'];
                $item->sku = $itemRec['ItemCode'];
                $item->title = $itemRec['ItemName'];
                $item->description = null;
                $item->barcode = $itemRec['BarCode'];
                $item->Extra1 = $itemRec['U_SubGrpNam'];
                $item->Extra2 = $itemRec['U_Color'];
                $item->Extra3 = $itemRec['U_Class'];
                $item->Extra4 = $itemRec['U_Input'];
                $result->products[] = $item;
            }
            if (isset($response['@odata.nextLink'])) {
                $urlQuery = '/'.$response['@odata.nextLink'];
            } else {
                $urlQuery = null;
            }
        } while ($urlQuery);

        return $result;
    }

    public function GetSubProducts(): ProductsDto
    {
        // TODO: Implement GetSubProducts() method.
    }

    public function GetUsers(): UsersDto
    {
        $endpoint = "/BusinessPartners";
        $queryParameters = [
            '$select' => "CardCode,CardName,Address,Cellular,Phone1,City,DiscountPercent,Block,FederalTaxID,AgentCode,CreditLimit,Valid,VatLiable,Frozen",
        ];
        $queryString = http_build_query($queryParameters);
        $urlQuery = $endpoint . '?' . $queryString;

        $result = new UsersDto();

        do {
            $response = $this->GetRequest($urlQuery);
            foreach ($response['value'] as $itemRec) {
                $item = new UserDto();
                $item->userExId = $itemRec['CardCode'];
                $item->userDescription = $itemRec['CardName'];
                $item->name = $itemRec['CardName'];
                $item->telephone = $itemRec['Phone1'];
                $item->phone = $itemRec['Cellular'];
                $item->address = $itemRec['Address'];
                $item->town = $itemRec['City'];
                $item->globalDiscount = $itemRec['DiscountPercent'];
                $item->isBlocked = $itemRec['Frozen'] === 'tYES';
                $item->isVatEnabled = $itemRec['VatLiable'] === 'vExempted' ? false : true ;
                $item->payCode = null; //TODO
                $item->payDes = null; //TODO
                $item->maxCredit = $itemRec['CreditLimit'];
                $item->maxObligo = 0;
                $item->hp = $itemRec['FederalTaxID'];
                $item->taxCode = '';
                $item->agentCode = $itemRec['AgentCode'];
                $result->users[] = $item;
            }
            if (isset($response['@odata.nextLink'])) {
                $urlQuery = '/'.$response['@odata.nextLink'];
            } else {
                $urlQuery = null;
            }
        } while ($urlQuery);

        return $result;
    }

    public function GetMigvan(): MigvansDto
    {
        // TODO: Implement GetMigvan() method.
    }

    public function GetPriceList(): PriceListsDto
    {
        $endpoint = "/PriceLists";
        $queryParameters = [
            '$select' => "PriceListNo,PriceListName,ValidTo"
        ];
        $queryString = http_build_query($queryParameters);
        $urlQuery = $endpoint . '?' . $queryString;

        $result = new PriceListsDto();

        do {
            $response = $this->GetRequest($urlQuery);
            foreach ($response['value'] as $itemRec) {
                $item = new PriceListDto();
                $item->priceListExtId = $itemRec['PriceListNo'];
                $item->priceListTitle = $itemRec['PriceListName'];
                $item->priceListExperationDate = $itemRec['ValidTo'];
                $result->priceLists[] = $item;
            }
            if (isset($response['@odata.nextLink'])) {
                $urlQuery = '/'.$response['@odata.nextLink'];  // Use the nextLink URL to fetch the next page
            } else {
                $urlQuery = null;  // No more pages, exit the loop
            }
        } while ($urlQuery);

        return $result;
    }

    public function GetPriceListUser(): PriceListsUserDto
    {
        $endpoint = "/BusinessPartners";
        $queryParameters = [
            '$select' => "CardCode,PriceListNum"
        ];
        $queryString = http_build_query($queryParameters);
        $urlQuery = $endpoint . '?' . $queryString;
        $result = new PriceListsUserDto();
        do {
            $response = $this->GetRequest($urlQuery);
            foreach ($response['value'] as $itemRec) {
                $item = new PriceListUserDto();
                $item->userExId = $itemRec['CardCode'];
                $item->priceListExId = $itemRec['PriceListNum'];
                $result->priceLists[] = $item;
            }
            if (isset($response['@odata.nextLink'])) {
                $urlQuery = '/'.$response['@odata.nextLink'];
            } else {
                $urlQuery = null;
            }
        } while ($urlQuery);

        return $result;
    }

    public function GetPriceListDetailed(): PriceListsDetailedDto
    {
        // TODO: Implement GetPriceListDetailed() method.
    }

    public function GetStocks(): StocksDto
    {
        // TODO: Implement GetStocks() method.
    }

    public function GetPackMain(): PacksMainDto
    {
        // TODO: Implement GetPackMain() method.
    }

    public function GetPackProducts(): PacksProductDto
    {
        // TODO: Implement GetPackProducts() method.
    }

    public function GetAgents(): UsersDto
    {
        $endpoint = "/SalesPersons";
        $queryParameters = [
            '$select' => "SalesEmployeeCode,SalesEmployeeName,Telephone,Mobile,Active"
        ];
        $queryString = http_build_query($queryParameters);
        $urlQuery = $endpoint . '?' . $queryString;

        $result = new UsersDto();
        do {
            // Make the request to fetch data
            $response = $this->GetRequest($urlQuery);

            // Process the response
            foreach ($response['value'] as $itemRec) {
                $item = new UserDto();
                $item->userExId = $itemRec['SalesEmployeeCode'];
                $item->name = $itemRec['SalesEmployeeName'];
                $item->phone = $itemRec['Mobile'];
                $item->telephone = $itemRec['Telephone'];
                $item->isBlocked = $itemRec['Active'] !== 'tYES';
                $result->users[] = $item;
            }

            // Check if there's another page of results
            if (isset($response['@odata.nextLink'])) {
                $urlQuery = '/'.$response['@odata.nextLink'];
            } else {
                $urlQuery = null;
            }
        } while ($urlQuery);

        return $result;
    }


    /** CUSTOM */
    public function GetDeliveryDates(string $userExtId)
    {
        $endpoint = "/SQLQueries('GetDetailsByZoneAndDelivery')/List";
        $queryParameters = [
            'cardCode' => "'$userExtId'"
        ];
        $queryString = http_build_query($queryParameters);
        $urlQuery = $endpoint . '?' . $queryString;
        $response = $this->GetRequest($urlQuery);
        $result = [];
        foreach ($response['value'] as $itemRec) {
            $obj = new \stdClass();
            $obj->area = $itemRec['U_DelArea'];
            $obj->city = $itemRec['city'];
            $obj->hour = $itemRec['hour'];
            $obj->street = $itemRec['street'];
            $obj->weekDay = $itemRec['weekDay'];
            $obj->zoneCode = $itemRec['zoneCode'];
            $result[] = $obj;
        }
        return $result;
    }


    public function ProductsImBuy(string $userExtId): array
    {
        $endpoint = "/Invoices";
        $queryParameters = [
            '$filter' => "CardCode eq '$userExtId'",
            '$select' => 'DocumentLines',
        ];

        $queryString = http_build_query($queryParameters);
        $urlQuery = $endpoint . '?' . $queryString;
        $response = $this->GetRequest($urlQuery);
        $result = [];
        foreach ($response['value'] as $itemRec) {
            foreach ($itemRec['DocumentLines'] as $subItem) {
                $result[] = $subItem['ItemCode'];
            }
        }

        $result = array_unique($result);

        $result = array_values($result);
        return $result;
    }

    public function ProductsImNotBuy(string $userExtId): array
    {
        // TODO: Implement ProductsImNotBuy() method.
    }

    public function GetBonuses(): BonusesDto
    {
        // TODO: Implement GetBonuses() method.
    }
}