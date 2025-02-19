<?php

namespace App\Erp\Core\Priority;
use App\Entity\User;
use App\Enum\DocumentsTypePriority;
use App\Enum\UsersTypes;
use App\Erp\Core\Dto\DocumentDto;
use App\Erp\Core\Dto\DocumentItemDto;
use App\Erp\Core\Dto\DocumentItemFileDto;
use App\Erp\Core\Dto\DocumentItemsDto;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PriorityDocuments extends Priority
{
    public function __construct(string $url, string $username, string $password, HttpClientInterface $httpClient)
    {
        parent::__construct($url, $username, $password, $httpClient);
        $this->httpClient = $httpClient;
    }

    public function GetOrders(?User $user, \DateTimeImmutable $dateFrom , \DateTimeImmutable $dateTo)
    {
        $endpoint = "/ORDERS";
        $dateFrom = $dateFrom->format('Y-m-d\TH:i:s.u\Z');
        $dateTo = $dateTo->format('Y-m-d\TH:i:s.u\Z');
        $queryParameters = [
            '$filter' => "CURDATE ge $dateFrom and CURDATE le $dateTo",
            '$select' => "CDES,CUSTNAME,QPRICE,CURDATE,ORDSTATUSDES,ORDNAME,STATUSDATE,AGENTCODE,AGENTNAME"
        ];
        if ($user) {
            $userExtId = $user->getExtId();
            if($user->getRole() == UsersTypes::AGENT || $user->getRole() == UsersTypes::SUPER_AGENT) {
                $queryParameters['$filter'] .= " and AGENTCODE eq '$userExtId'";
            } else {
                $queryParameters['$filter'] .= " and CUSTNAME eq '$userExtId'";
            }
        }

        $queryString = http_build_query($queryParameters);
        $urlQuery = $endpoint . '?' . $queryString;
        $response = $this->GetRequest($urlQuery);
        $result = [];
        foreach ($response as $itemRec) {
            $dto = new DocumentDto();
            $dto->userName = $itemRec['CDES'];
            $dto->userExId = $itemRec['CUSTNAME'];
            $dto->agentExId = $itemRec['AGENTCODE'];
            $dto->agentName = $itemRec['AGENTNAME'];
            $dto->total = $itemRec['QPRICE'];
            $dto->createdAt = $itemRec['CURDATE'];
            $dto->documentType = DocumentsTypePriority::ORDERS->value;
            $dto->status = $itemRec['ORDSTATUSDES'];
            $dto->documentNumber = $itemRec['ORDNAME'];
            $dto->updatedAt = $itemRec['STATUSDATE'];
            $result[] = $dto;
        }

        return $result;
    }

    public function GetOrderItems(string $documentNumber): DocumentItemsDto
    {
        $endpoint = "/ORDERS";
        $queryParameters = [

            '$filter' => "ORDNAME eq '$documentNumber'",
            '$expand' => 'ORDERITEMS_SUBFORM($select=PARTNAME,PDES,PRICE,QUANT,TQUANT,QPRICE,PERCENT),EXTFILES_SUBFORM',
        ];


        $queryString = http_build_query($queryParameters);
        $urlQuery = $endpoint . '?' . $queryString;
        $response = $this->GetRequest($urlQuery);
        $result = new DocumentItemsDto();
        foreach ($response as $itemRec) {
            $result->totalAfterDiscount = $itemRec['DISPRICE'];
            $result->totalPrecent = $itemRec['PERCENT'];
            $result->totalPriceAfterTax = $itemRec['TOTPRICE'];
            $result->totalTax = $itemRec['VAT'];
            $result->documentType = DocumentsTypePriority::ORDERS->value;
            foreach ($itemRec['ORDERITEMS_SUBFORM'] as $subItem){
                $dto = new DocumentItemDto();
                $dto->sku = $subItem['PARTNAME'];
                $dto->title = $subItem['PDES'];
                $dto->quantity = $subItem['TQUANT'];
                $dto->priceByOne = $subItem['PRICE'];
                $dto->total = $subItem['QPRICE'];
                $dto->discount = $subItem['PERCENT'];
                $result->products[] = $dto;
            }
            foreach ($itemRec['EXTFILES_SUBFORM'] as $fileRec){
                if($fileRec['SUFFIX'] == 'pdf') {
                    $obj = new DocumentItemFileDto();
                    $obj->name = $fileRec['EXTFILEDES'];
                    $obj->base64 = $fileRec['EXTFILENAME'];
                    $result->files[] = $obj;
                }
            }
        }


        return $result;
    }

    public function GetPriceOffer(?User $user, \DateTimeImmutable $dateFrom , \DateTimeImmutable $dateTo)
    {
        $endpoint = "/CPROF";
        $dateFrom = $dateFrom->format('Y-m-d\TH:i:s.u\Z');
        $dateTo = $dateTo->format('Y-m-d\TH:i:s.u\Z');

        $queryParameters = [
            '$filter' => "PDATE ge $dateFrom and PDATE le $dateTo",
        ];

        if ($user) {
            $userExtId = $user->getExtId();
            if($user->getRole() == UsersTypes::AGENT || $user->getRole() == UsersTypes::SUPER_AGENT) {
                $queryParameters['$filter'] .= " and AGENTCODE eq '$userExtId'";
            } else {
                $queryParameters['$filter'] .= " and CUSTNAME eq '$userExtId'";
            }
        }


        $queryString = http_build_query($queryParameters);
        $urlQuery = $endpoint . '?' . $queryString;

        $response = $this->GetRequest($urlQuery);

        $result = [];
        foreach ($response as $itemRec) {
            $dto = new DocumentDto();
            $dto->userName = $itemRec['CDES'];
            $dto->userExId = $itemRec['CUSTNAME'];
            $dto->total = $itemRec['QPRICE'];
            $dto->createdAt = $itemRec['PDATE'];
            $dto->documentType = DocumentsTypePriority::PRICE_OFFER->value;
            $dto->status = $itemRec['STATDES'];
            $dto->documentNumber = $itemRec['CPROFNUM'];
            $dto->updatedAt = $itemRec['EXPIRYDATE'];
            $result[] = $dto;
        }

        return $result;
    }

    public function GetPriceOfferItem(string $documentNumber): DocumentItemsDto
    {
        $endpoint = "/CPROF";
        $queryParameters = [
            '$filter' => "CPROFNUM eq '$documentNumber'",
            '$expand' => "CPROFITEMS_SUBFORM,EXTFILES_SUBFORM"
        ];


        $queryString = http_build_query($queryParameters);
        $urlQuery = $endpoint . '?' . $queryString;
        $response = $this->GetRequest($urlQuery);
        $result = new DocumentItemsDto();
        foreach ($response as $itemRec) {
            $result->totalAfterDiscount = $itemRec['DISPRICE'];
            $result->totalPrecent = $itemRec['PERCENT'];
            $result->totalPriceAfterTax = $itemRec['TOTPRICE'];
            $result->totalTax = $itemRec['VAT'];
            $result->documentType = DocumentsTypePriority::PRICE_OFFER->value;
            foreach ($itemRec['CPROFITEMS_SUBFORM'] as $subItem){
                $dto = new DocumentItemDto();
                $dto->sku = $subItem['PARTNAME'];
                $dto->title = $subItem['PDES'];
                $dto->quantity = $subItem['TQUANT'];
                $dto->priceByOne = $subItem['PRICE'];
                $dto->total = $subItem['QPRICE'];
                $dto->discount = $subItem['PERCENT'];
                $result->products[] = $dto;
            }
            foreach ($itemRec['EXTFILES_SUBFORM'] as $fileRec){
                if($fileRec['SUFFIX'] == 'pdf') {
                    $obj = new DocumentItemFileDto();
                    $obj->name = $fileRec['EXTFILEDES'];
                    $obj->base64 = $fileRec['EXTFILENAME'];
                    $result->files[] = $obj;
                }
            }
        }

        return $result;
    }

    public function GetDeliveryOrder(?User $user, \DateTimeImmutable $dateFrom , \DateTimeImmutable $dateTo)
    {
        $endpoint = "/DOCUMENTS_D";
        $dateFrom = $dateFrom->format('Y-m-d\TH:i:s.u\Z');
        $dateTo = $dateTo->format('Y-m-d\TH:i:s.u\Z');

        $queryParameters = [
            '$filter' => "CURDATE ge $dateFrom and CURDATE le $dateTo",
        ];

        if ($user) {
            $userExtId = $user->getExtId();
            if($user == UsersTypes::AGENT || $user == UsersTypes::SUPER_AGENT) {
                $queryParameters['$filter'] .= " and AGENTCODE eq '$userExtId'";
            } else {
                $queryParameters['$filter'] .= " and CUSTNAME eq '$userExtId'";
            }
        }

        $queryString = http_build_query($queryParameters);
        $urlQuery = $endpoint . '?' . $queryString;
        $response = $this->GetRequest($urlQuery);

        $result = [];
        foreach ($response as $itemRec) {
            $dto = new DocumentDto();
            $dto->userName = $itemRec['CDES'];
            $dto->userExId = $itemRec['CUSTNAME'];
            $dto->total = $itemRec['TOTQUANT'];
            $dto->createdAt = $itemRec['CURDATE'];
            $dto->status = $itemRec['STATDES'];
            $dto->documentType = DocumentsTypePriority::DELIVERY_ORDER->value;
            $dto->documentNumber = $itemRec['DOCNO'];
            $dto->updatedAt = $itemRec['UDATE'];
            $result[] = $dto;
        }

        return $result;
    }

    public function GetDeliveryOrderItem(string $documentNumber): DocumentItemsDto
    {
        $endpoint = "/DOCUMENTS_D";
        $queryParameters = [
            '$filter' => "DOCNO eq '$documentNumber'",
            '$expand' => "TRANSORDER_D_SUBFORM,EXTFILES_SUBFORM"
        ];


        $queryString = http_build_query($queryParameters);
        $urlQuery = $endpoint . '?' . $queryString;
        $response = $this->GetRequest($urlQuery);
        $result = new DocumentItemsDto();
        foreach ($response as $itemRec) {
            $result->totalAfterDiscount = $itemRec['DISPRICE'];
            $result->totalPrecent = $itemRec['PERCENT'];
            $result->totalPriceAfterTax = $itemRec['TOTPRICE'];
            $result->totalTax = $itemRec['VAT'];
            $result->documentType = DocumentsTypePriority::RETURN_ORDERS->value;
            foreach ($itemRec['TRANSORDER_D_SUBFORM'] as $subItem) {
                $dto = new DocumentItemDto();
                $dto->sku = $subItem['PARTNAME'];
                $dto->quantity = $subItem['TQUANT'];
                $dto->title = $subItem['PDES'];
                $dto->priceByOne = $subItem['PRICE'];
                $dto->total = $subItem['QPRICE'];
                $dto->discount = $subItem['PERCENT'];

                $result->products[] = $dto;
            }
            foreach ($itemRec['EXTFILES_SUBFORM'] as $fileRec){
                if($fileRec['SUFFIX'] == 'pdf') {
                    $obj = new DocumentItemFileDto();
                    $obj->name = $fileRec['EXTFILEDES'];
                    $obj->base64 = $fileRec['EXTFILENAME'];
                    $result->files[] = $obj;
                }
            }
        }

        return $result;
    }

    public function GetAiInvoice(?User $user, \DateTimeImmutable $dateFrom , \DateTimeImmutable $dateTo)
    {
        $endpoint = "/AINVOICES";
        $dateFrom = $dateFrom->format('Y-m-d\TH:i:s.u\Z');
        $dateTo = $dateTo->format('Y-m-d\TH:i:s.u\Z');
        $queryParameters = [
            '$filter' => "IVDATE ge $dateFrom and IVDATE le $dateTo",
        ];

        if ($user) {
            $userExtId = $user->getExtId();
            if($user->getRole() == UsersTypes::AGENT || $user->getRole() == UsersTypes::SUPER_AGENT) {
                $queryParameters['$filter'] .= " and AGENTCODE eq '$userExtId'";
            } else {
                $queryParameters['$filter'] .= " and CUSTNAME eq '$userExtId'";
            }
        }

        $queryString = http_build_query($queryParameters);
        $urlQuery = $endpoint . '?' . $queryString;
        $response = $this->GetRequest($urlQuery);

        $result = [];
        foreach ($response as $itemRec) {
            $dto = new DocumentDto();
            $dto->userName = $itemRec['CDES'];
            $dto->userExId = $itemRec['CUSTNAME'];
            $dto->total = $itemRec['QPRICE'];
            $dto->createdAt = $itemRec['IVDATE'];
            $dto->documentType = DocumentsTypePriority::AI_INVOICE->value;
            $dto->status = $itemRec['STATDES'];
            $dto->documentNumber = $itemRec['IVNUM'];
            $dto->updatedAt = $itemRec['IVDATE'];
            $result[] = $dto;
        }

        return $result;
    }

    public function GetAiInvoiceItem(string $documentNumber): DocumentItemsDto
    {
        $endpoint = "/AINVOICES";
        $queryParameters = [
            '$filter' => "IVNUM eq '$documentNumber'",
            '$expand' => "AINVOICEITEMS_SUBFORM,EXTFILES_SUBFORM"
        ];


        $queryString = http_build_query($queryParameters);
        $urlQuery = $endpoint . '?' . $queryString;
        $response = $this->GetRequest($urlQuery);
        $result = new DocumentItemsDto();
        foreach ($response as $itemRec) {
            $result->totalAfterDiscount = $itemRec['DISPRICE'];
            $result->totalPrecent = $itemRec['PERCENT'];
            $result->totalPriceAfterTax = $itemRec['TOTPRICE'];
            $result->totalTax = $itemRec['VAT'];
            $result->documentType = DocumentsTypePriority::AI_INVOICE->value;
            foreach ($itemRec['AINVOICEITEMS_SUBFORM'] as $subItem) {
                $dto = new DocumentItemDto();
                $dto->sku = $subItem['PARTNAME'];
                $dto->title = $subItem['PDES'];
                $dto->quantity = $subItem['TQUANT'];
                $dto->priceByOne = $subItem['PRICE'];
                $dto->total = $subItem['QPRICE'];
                $dto->discount = $subItem['PERCENT'];
                $result->products[] = $dto;
            }

            foreach ($itemRec['EXTFILES_SUBFORM'] as $fileRec){
                if($fileRec['SUFFIX'] == 'pdf') {
                    $obj = new DocumentItemFileDto();
                    $obj->name = $fileRec['EXTFILEDES'];
                    $obj->base64 = $fileRec['EXTFILENAME'];
                    $result->files[] = $obj;
                }
            }
        }


        return $result;
    }

    public function GetCiInvoice(?User $user, \DateTimeImmutable $dateFrom , \DateTimeImmutable $dateTo)
    {
        $endpoint = "/CINVOICES";
        $dateFrom = $dateFrom->format('Y-m-d\TH:i:s.u\Z');
        $dateTo = $dateTo->format('Y-m-d\TH:i:s.u\Z');
        $queryParameters = [
            '$filter' => "IVDATE ge $dateFrom and IVDATE le $dateTo",
        ];

        if ($user) {
            $userExtId = $user->getExtId();
            if($user->getRole() == UsersTypes::AGENT || $user->getRole() == UsersTypes::SUPER_AGENT) {
                $queryParameters['$filter'] .= " and AGENTCODE eq '$userExtId'";
            } else {
                $queryParameters['$filter'] .= " and CUSTNAME eq '$userExtId'";
            }
        }


        $queryString = http_build_query($queryParameters);
        $urlQuery = $endpoint . '?' . $queryString;
        $response = $this->GetRequest($urlQuery);
        $result = [];
        foreach ($response as $itemRec) {
            $dto = new DocumentDto();
            $dto->userName = $itemRec['CDES'];
            $dto->userExId = $itemRec['CUSTNAME'];
            $dto->total = $itemRec['TOTPRICE'];
            $dto->createdAt = $itemRec['IVDATE'];
            $dto->documentType = DocumentsTypePriority::CI_INVOICE->value;
            $dto->status = $itemRec['STATDES'];
            $dto->documentNumber = $itemRec['IVNUM'];
            $dto->updatedAt = $itemRec['IVDATE'];
            $result[] = $dto;
        }

        return $result;
    }

    public function GetCiInvoiceItem(string $documentNumber): DocumentItemsDto
    {
        $endpoint = "/CINVOICES";
        $queryParameters = [
            '$filter' => "IVNUM eq '$documentNumber'",
            '$expand' => "CINVOICEITEMS_SUBFORM,EXTFILES_SUBFORM"
        ];


        $queryString = http_build_query($queryParameters);
        $urlQuery = $endpoint . '?' . $queryString;
        $response = $this->GetRequest($urlQuery);
        $result = new DocumentItemsDto();
        foreach ($response as $itemRec) {
            $result->totalAfterDiscount = $itemRec['DISPRICE'];
            $result->totalPrecent = $itemRec['PERCENT'];
            $result->totalPriceAfterTax = $itemRec['TOTPRICE'];
            $result->totalTax = $itemRec['VAT'];
            $result->documentType = DocumentsTypePriority::CI_INVOICE->value;
            foreach ($itemRec['CINVOICEITEMS_SUBFORM'] as $subItem) {
                $dto = new DocumentItemDto();
                $dto->sku = $subItem['PARTNAME'];
                $dto->quantity = $subItem['TQUANT'];
                $dto->title = $subItem['PDES'];
                $dto->priceByOne = $subItem['PRICE'];
                $dto->total = $subItem['QPRICE'];
                $dto->discount = $subItem['PERCENT'];
                $result->products[] = $dto;
            }
            foreach ($itemRec['EXTFILES_SUBFORM'] as $fileRec){
                if($fileRec['SUFFIX'] == 'pdf') {
                    $obj = new DocumentItemFileDto();
                    $obj->name = $fileRec['EXTFILEDES'];
                    $obj->base64 = $fileRec['EXTFILENAME'];
                    $result->files[] = $obj;
                }
            }
        }


        return $result;
    }

    public function GetReturnDocs(?User $user, \DateTimeImmutable $dateFrom , \DateTimeImmutable $dateTo)
    {
        $endpoint = "/DOCUMENTS_N";
        $dateFrom = $dateFrom->format('Y-m-d\TH:i:s.u\Z');
        $dateTo = $dateTo->format('Y-m-d\TH:i:s.u\Z');
        $queryParameters = [
            '$filter' => "CURDATE ge $dateFrom and CURDATE le $dateTo",
        ];

        if ($user) {
            $userExtId = $user->getExtId();
            if($user->getRole() == UsersTypes::AGENT || $user->getRole() == UsersTypes::SUPER_AGENT) {
                $queryParameters['$filter'] .= " and AGENTCODE eq '$userExtId'";
            } else {
                $queryParameters['$filter'] .= " and CUSTNAME eq '$userExtId'";
            }
        }

        $queryString = http_build_query($queryParameters);
        $urlQuery = $endpoint . '?' . $queryString;
        $response = $this->GetRequest($urlQuery);
        $result = [];
        foreach ($response as $itemRec) {
            $dto = new DocumentDto();
            $dto->userName = $itemRec['CDES'];
            $dto->userExId = $itemRec['CUSTNAME'];
            $dto->total = $itemRec['TOTPRICE'];
            $dto->createdAt = $itemRec['CURDATE'];
            $dto->documentType = DocumentsTypePriority::RETURN_ORDERS->value;
            $dto->status = $itemRec['STATDES'];
            $dto->documentNumber = $itemRec['DOCNO'];
            $dto->updatedAt = $itemRec['UDATE'];
            $result[] = $dto;
        }

        return $result;
    }

    public function GetReturnDocsItem(string $documentNumber): DocumentItemsDto
    {
        $endpoint = "/DOCUMENTS_N";
        $queryParameters = [
            '$filter' => "DOCNO eq '$documentNumber'",
            '$expand' => "TRANSORDER_N_SUBFORM,EXTFILES_SUBFORM"
        ];


        $queryString = http_build_query($queryParameters);
        $urlQuery = $endpoint . '?' . $queryString;
        $response = $this->GetRequest($urlQuery);
        $result = new DocumentItemsDto();
        foreach ($response as $itemRec) {
            $result->totalAfterDiscount = $itemRec['DISPRICE'];
            $result->totalPrecent = $itemRec['PERCENT'];
            $result->totalPriceAfterTax = $itemRec['TOTPRICE'];
            $result->totalTax = $itemRec['VAT'];
            $result->documentType = DocumentsTypePriority::RETURN_ORDERS->value;
            foreach ($itemRec['TRANSORDER_N_SUBFORM'] as $subItem) {
                $dto = new DocumentItemDto();
                $dto->sku = $subItem['PARTNAME'];
                $dto->quantity = $subItem['TQUANT'];
                $dto->title = $subItem['PDES'];
                $dto->priceByOne = $subItem['PRICE'];
                $dto->total = $subItem['QPRICE'];
                $dto->discount = $subItem['PERCENT'];
                $result->products[] = $dto;
            }
            foreach ($itemRec['EXTFILES_SUBFORM'] as $fileRec){
                if($fileRec['SUFFIX'] == 'pdf') {
                    $obj = new DocumentItemFileDto();
                    $obj->name = $fileRec['EXTFILEDES'];
                    $obj->base64 = $fileRec['EXTFILENAME'];
                    $result->files[] = $obj;
                }
            }
        }


        return $result;
    }
}