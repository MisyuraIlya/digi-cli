<?php

namespace App\Erp\Core;

use App\Entity\History;
use App\Entity\User;
use App\Erp\Core\Dto\AgentStatisticDto;
use App\Erp\Core\Dto\CartessetDto;
use App\Erp\Core\Dto\CategoriesDto;
use App\Erp\Core\Dto\DocumentItemsDto;
use App\Erp\Core\Dto\DocumentsDto;
use App\Erp\Core\Dto\HovotDto;
use App\Erp\Core\Dto\MigvansDto;
use App\Erp\Core\Dto\MoneyInfo;
use App\Erp\Core\Dto\PacksMainDto;
use App\Erp\Core\Dto\PacksProductDto;
use App\Erp\Core\Dto\PriceListsDetailedDto;
use App\Erp\Core\Dto\PriceListsDto;
use App\Erp\Core\Dto\PriceListsUserDto;
use App\Erp\Core\Dto\PriceOnlineDto;
use App\Erp\Core\Dto\PricesDto;
use App\Erp\Core\Dto\ProductsDto;
use App\Erp\Core\Dto\PurchaseHistory;
use App\Erp\Core\Dto\SalesKeeperAlertDto;
use App\Erp\Core\Dto\SalesQuantityKeeperAlertDto;
use App\Erp\Core\Dto\StockDto;
use App\Erp\Core\Dto\StocksDto;
use App\Erp\Core\Dto\UsersDto;
use App\Erp\Core\Priority\Priority;
use App\Erp\Core\SAP\Sap;
use App\Erp\Core\Dto\BonusesDto;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ErpManager implements ErpInterface
{
    private $erp;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
    ) {
        $erpType = $_ENV['ERP_TYPE'];
        $username = $_ENV['ERP_USERNAME'];
        $password = $_ENV['ERP_PASSWORD'];
        $erpDb = $_ENV['ERP_DB'];
        $url = $_ENV['ERP_URL'];
        if ($erpType === 'Priority') {
            $this->erp = new Priority($url, $username, $password, $this->httpClient);
        } elseif ($erpType === 'SAP') {
            $this->erp = new SAP($url, $username, $password, $this->httpClient, $erpDb);
        } else {
            throw new \Exception("Unsupported ERP type: $erpType");
        }
    }

    /** CORE */
    public function GetRequest(?string $query)
    {
        return $this->erp->GetRequest($query);
    }

    public function PatchRequest(object $object, string $table)
    {
        return $this->erp->PatchRequest($object, $table);
    }

    public function PostRequest(\stdClass $object, string $table)
    {
        return $this->erp->PostRequest($object, $table);
    }

    /** ONLINE */
    public function SendOrder(History $history)
    {
        return $this->erp->SendOrder($history);
    }

    public function GetDocuments(\DateTimeImmutable $dateFrom, \DateTimeImmutable $dateTo, string $documentsType, int $pageSize, int $currentPage,?User $user, ?string $search = null): DocumentsDto
    {
        return $this->erp->GetDocuments($dateFrom, $dateTo, $documentsType,$pageSize, $currentPage, $user, $search);
    }

    public function GetDocumentsItem(string $documentNumber, string $documentType): DocumentItemsDto
    {
        return $this->erp->GetDocumentsItem($documentNumber, $documentType);
    }

    public function GetCartesset(string $userExId, \DateTimeImmutable $dateFrom, \DateTimeImmutable $dateTo): CartessetDto
    {
        return $this->erp->GetCartesset($userExId, $dateFrom, $dateTo);
    }

    public function GetHovot(string $userExId, \DateTimeImmutable $dateFrom, \DateTimeImmutable $dateTo): HovotDto
    {
        return $this->erp->GetHovot($userExId, $dateFrom, $dateTo);
    }

    public function PurchaseHistoryByUserAndSku(string $userExtId, string $sku): PurchaseHistory
    {
        return $this->erp->PurchaseHistoryByUserAndSku($userExtId, $sku);
    }

    public function GetAgentStatistic(string $agentId, string $dateFrom, string $dateTo): AgentStatisticDto
    {
        return $this->erp->GetAgentStatistic($agentId, $dateFrom, $dateTo);
    }

    public function SalesKeeperAlert(string $userExtId): SalesKeeperAlertDto
    {
        return $this->erp->SalesKeeperAlert($userExtId);
    }

    public function SalesQuantityKeeperAlert(string $userExtId): SalesQuantityKeeperAlertDto
    {
        return $this->erp->SalesQuantityKeeperAlert($userExtId);
    }

    public function GetPriceOnline(string $userExtId, string $sku, array|string $priceListNumber): PriceOnlineDto
    {
        return $this->erp->GetPriceOnline($userExtId,$sku,$priceListNumber);
    }

    public function GetStockOnline(string $sku, string $warehouse): StockDto
    {
        return $this->erp->GetStockOnline($sku,$warehouse);
    }

    public function UserMoneyInfo(string $userExtId): MoneyInfo
    {
        return $this->erp->UserMoneyInfo($userExtId);
    }

    public function ProductsImBuy(string $userExtId):array
    {
        return $this->erp->ProductsImBuy($userExtId);
    }

    public function ProductsImNotBuy(string $userExtId):array
    {
        return $this->erp->ProductsImNotBuy($userExtId);
    }


    /** FOR CRON */
    public function GetCategories(): CategoriesDto
    {
        return $this->erp->GetCategories();
    }

    public function GetProducts(?int $pageSize, ?int $skip): ProductsDto
    {
        return $this->erp->GetProducts($pageSize, $skip);
    }

    public function GetSubProducts(): ProductsDto
    {
        return $this->erp->GetSubProducts();
    }

    public function GetUsers(): UsersDto
    {
        return $this->erp->GetUsers();
    }

    public function GetUsersInfo(): UsersDto
    {
        return $this->erp->GetUsersInfo();
    }

    public function GetSubUsers(): UsersDto
    {
        return $this->erp->GetSubUsers();
    }

    public function GetMigvan(): MigvansDto
    {
        return $this->erp->GetMigvan();
    }

    public function GetPriceList(): PriceListsDto
    {
        return $this->erp->GetPriceList();
    }

    public function GetPriceListUser(): PriceListsUserDto
    {
        return $this->erp->GetPriceListUser();
    }

    public function GetPriceListDetailed(): PriceListsDetailedDto
    {
        return $this->erp->GetPriceListDetailed();
    }

    public function GetStocks(): StocksDto
    {
        return $this->erp->GetStocks();
    }

    public function GetPackMain(): PacksMainDto
    {
        return $this->erp->GetPackMain();
    }

    public function GetPackProducts(): PacksProductDto
    {
        return $this->erp->GetPackProducts();
    }

    public function GetAgents(): UsersDto
    {
        return $this->erp->GetAgents();
    }

    /** Additional Methods */
    public function GetProductImage(string $sku)
    {
        return $this->erp->GetProductImage($sku);
    }

    public function GetBonuses(): BonusesDto
    {
        return $this->erp->GetBonuses();
    }

}
