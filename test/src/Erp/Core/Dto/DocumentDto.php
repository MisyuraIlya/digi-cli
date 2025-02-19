<?php

namespace App\Erp\Core\Dto;


use App\Entity\User;

class DocumentDto
{
    public $id;
    public $documentNumber;
    public string $documentType;
    public $userName;
    public $userExId;
    public $agentExId;
    public $agentName;
    public $status;
    public $createdAt;
    public $updatedAt;
    public $total;

    public $user;

    public ?string $error;

}