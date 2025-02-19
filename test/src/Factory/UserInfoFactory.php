<?php

namespace App\Factory;

use App\Entity\UserInfo;
use App\Repository\UserInfoRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<UserInfo>
 *
 * @method        UserInfo|Proxy                     create(array|callable $attributes = [])
 * @method static UserInfo|Proxy                     createOne(array $attributes = [])
 * @method static UserInfo|Proxy                     find(object|array|mixed $criteria)
 * @method static UserInfo|Proxy                     findOrCreate(array $attributes)
 * @method static UserInfo|Proxy                     first(string $sortedField = 'id')
 * @method static UserInfo|Proxy                     last(string $sortedField = 'id')
 * @method static UserInfo|Proxy                     random(array $attributes = [])
 * @method static UserInfo|Proxy                     randomOrCreate(array $attributes = [])
 * @method static UserInfoRepository|RepositoryProxy repository()
 * @method static UserInfo[]|Proxy[]                 all()
 * @method static UserInfo[]|Proxy[]                 createMany(int $number, array|callable $attributes = [])
 * @method static UserInfo[]|Proxy[]                 createSequence(iterable|callable $sequence)
 * @method static UserInfo[]|Proxy[]                 findBy(array $attributes)
 * @method static UserInfo[]|Proxy[]                 randomRange(int $min, int $max, array $attributes = [])
 * @method static UserInfo[]|Proxy[]                 randomSet(int $number, array $attributes = [])
 */
final class UserInfoFactory extends ModelFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function getDefaults(): array
    {
        return [
            'user' => UserFactory::new(),
            'hp' => self::faker()->numberBetween(1000, 9000),
            'contact_name' => self::faker()->name(),
            'address' => self::faker()->address(),
            'town' => self::faker()->city(),
            'zip' => self::faker()->numberBetween(1000, 9000),
            'max_obligo' =>  self::faker()->numberBetween(1000, 10000),
            'min_price' =>  self::faker()->numberBetween(1000, 10000),
            'balance' =>  self::faker()->numberBetween(1000, 10000),
            'passport' =>  self::faker()->numberBetween(100000000, 900000000),
            'shotef' => self::faker()->numberBetween(1000, 9000),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(UserInfo $userInfo): void {})
        ;
    }

    protected static function getClass(): string
    {
        return UserInfo::class;
    }
}
