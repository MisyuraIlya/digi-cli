<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Entity\User;
use App\State\CartessetProvider;
use ApiPlatform\Metadata\Link;

#[ApiResource(
    uriTemplate: '/cartesset/{dateFrom}/{dateTo}/{userExtId}',
    shortName: 'Cartesset',
    operations: [
        new Get()
    ],
    uriVariables: [
        'dateFrom' => new Link(fromClass: Date::class),
        'dateTo' => new Link(fromClass: Date::class),
        'userExtId' => new Link(fromClass: User::class),
    ],
    provider: CartessetProvider::class
)]
class Cartesset
{
    public ?string $userExtId;

    public function __construct(string $userExtId)
    {
        $this->userExtId = $userExtId;
    }

    #[ApiProperty(identifier: true)]
    public function getUserExtId(): string
    {
        return $this->userExtId;
    }
}