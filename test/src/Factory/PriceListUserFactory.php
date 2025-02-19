<?php

namespace App\Factory;

use App\Entity\PriceListUser;
use App\Repository\PriceListUserRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<PriceListUser>
 *
 * @method        PriceListUser|Proxy                     create(array|callable $attributes = [])
 * @method static PriceListUser|Proxy                     createOne(array $attributes = [])
 * @method static PriceListUser|Proxy                     find(object|array|mixed $criteria)
 * @method static PriceListUser|Proxy                     findOrCreate(array $attributes)
 * @method static PriceListUser|Proxy                     first(string $sortedField = 'id')
 * @method static PriceListUser|Proxy                     last(string $sortedField = 'id')
 * @method static PriceListUser|Proxy                     random(array $attributes = [])
 * @method static PriceListUser|Proxy                     randomOrCreate(array $attributes = [])
 * @method static PriceListUserRepository|RepositoryProxy repository()
 * @method static PriceListUser[]|Proxy[]                 all()
 * @method static PriceListUser[]|Proxy[]                 createMany(int $number, array|callable $attributes = [])
 * @method static PriceListUser[]|Proxy[]                 createSequence(iterable|callable $sequence)
 * @method static PriceListUser[]|Proxy[]                 findBy(array $attributes)
 * @method static PriceListUser[]|Proxy[]                 randomRange(int $min, int $max, array $attributes = [])
 * @method static PriceListUser[]|Proxy[]                 randomSet(int $number, array $attributes = [])
 */
final class PriceListUserFactory extends ModelFactory
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
            'priceList' => PriceListFactory::new(),
            'expiredAt' =>  \DateTimeImmutable::createFromMutable(self::faker()->dateTime())
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(PriceListUser $priceListUser): void {})
        ;
    }

    protected static function getClass(): string
    {
        return PriceListUser::class;
    }
}
