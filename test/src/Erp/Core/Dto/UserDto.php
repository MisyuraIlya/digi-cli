<?php

namespace App\Erp\Core\Dto;

class UserDto
{
    public ?string $userExId;
    public ?string $userDescription;
    public ?string $name;
    public ?string $telephone;
    public ?string $phone;
    public ?string $address;
    public ?string $town;
    public ?int $globalDiscount;
    public ?bool $isBlocked;

    public ?string $payCode;
    public ?string $payDes;
    public ?float $maxCredit;
    public ?float $maxObligo;
    public ?string $hp;
    public ?string $taxCode;
    public ?string $agentCode;
    public ?bool $isVatEnabled;

    /** @var UserDto[] */
    public $subUsers = [];
}