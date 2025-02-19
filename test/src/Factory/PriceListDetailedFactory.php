<?php

namespace App\Factory;

use App\Entity\PriceListDetailed;
use App\Repository\PriceListDetailedRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<PriceListDetailed>
 *
 * @method        PriceListDetailed|Proxy                     create(array|callable $attributes = [])
 * @method static PriceListDetailed|Proxy                     createOne(array $attributes = [])
 * @method static PriceListDetailed|Proxy                     find(object|array|mixed $criteria)
 * @method static PriceListDetailed|Proxy                     findOrCreate(array $attributes)
 * @method static PriceListDetailed|Proxy                     first(string $sortedField = 'id')
 * @method static PriceListDetailed|Proxy                     last(string $sortedField = 'id')
 * @method static PriceListDetailed|Proxy                     random(array $attributes = [])
 * @method static PriceListDetailed|Proxy                     randomOrCreate(array $attributes = [])
 * @method static PriceListDetailedRepository|RepositoryProxy repository()
 * @method static PriceListDetailed[]|Proxy[]                 all()
 * @method static PriceListDetailed[]|Proxy[]                 createMany(int $number, array|callable $attributes = [])
 * @method static PriceListDetailed[]|Proxy[]                 createSequence(iterable|callable $sequence)
 * @method static PriceListDetailed[]|Proxy[]                 findBy(array $attributes)
 * @method static PriceListDetailed[]|Proxy[]                 randomRange(int $min, int $max, array $attributes = [])
 * @method static PriceListDetailed[]|Proxy[]                 randomSet(int $number, array $attributes = [])
 */
final class PriceListDetailedFactory extends ModelFactory
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
            'price' => self::faker()->numberBetween(1, 2000),
            'product' => ProductFactory::new(),
            'discount' => self::faker()->numberBetween(0, 100),
            'priceList' => PriceListFactory::new(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(PriceListDetailed $priceListDetailed): void {})
        ;
    }

    protected static function getClass(): string
    {
        return PriceListDetailed::class;
    }
}
