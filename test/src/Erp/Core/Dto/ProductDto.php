<?php

namespace App\Erp\Core\Dto;

class ProductDto
{
    public ?string $sku;
    public ?string $barcode;
    public ?string $title;
    public ?string $description;
    public ?string $categoryLvl1Id;
    public ?string $categoryLvl1Name;
    public ?string $categoryLvl2Id;
    public ?string $categoryLvl2Name;
    public ?string $categoryLvl3Id;
    public ?string $categoryLvl3Name;
    public ?string $categoryDescription;
    public bool $status;
    public ?string $baseprice;
    public ?float $minimumPrice;
    public bool $intevntory_managed;
    public ?string $parent;
    public ?int $packQuantity;

//    CUSTOM

    public ?bool $isHumane;
    public ?bool $isVetrinary;
    public ?bool $isPharamecies;
    public ?bool $isMedicalCenter;
    public ?bool $isHospital;

    public ?string $link;

    public ?string $linkTitle;

    public ?bool $isDrugNotInBasket;

    public ?string $Extra1;
    public ?string $Extra2;
    public ?string $Extra3;
    public ?string $Extra4;
    public ?string $Extra5;
    public ?string $Extra6;
    public ?string $Extra7;
    public ?string $Extra8;
    public ?string $Extra9;
    public ?string $Extra10;
    public ?string $Extra11;
    public ?string $Extra12;
    public ?string $Extra13;
    public ?string $Extra14;
    public ?string $Extra15;
    public ?string $Extra16;
    public ?string $Extra17;
    public ?string $Extra18;
    public ?string $Extra19;
    public ?string $Extra20;


    //CUSTOM
    public ?string $innerHtml;

}