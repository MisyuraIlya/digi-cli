<?php

namespace App\Erp\Core\Dto;

class CategoryDto
{
    public ?string $categoryName;

    public ?string $categoryId;
    public ?string $parentId;
    public ?string $parentName;
    public ?int $lvlNumber;
}