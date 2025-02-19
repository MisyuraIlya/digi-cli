<?php

namespace App\Erp\Core\Dto;

class DocumentsDto
{
    /** @var DocumentDto[] */
    public $documents = [];

    public ?int $totalRecords = null;
    public ?int $totalPages = null;
    public ?int $currentPage = null;
    public ?int $pageSize = null;


//    public $selectBox = [];
}