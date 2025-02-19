<?php

namespace App\Factory;

use App\Entity\ProductPack;
use App\Repository\ProductPackRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<ProductPack>
 *
 * @method        ProductPack|Proxy                     create(array|callable $attributes = [])
 * @method static ProductPack|Proxy                     createOne(array $attributes = [])
 * @method static ProductPack|Proxy                     find(object|array|mixed $criteria)
 * @method static ProductPack|Proxy                     findOrCreate(array $attributes)
 * @method static ProductPack|Proxy                     first(string $sortedField = 'id')
 * @method static ProductPack|Proxy                     last(string $sortedField = 'id')
 * @method static ProductPack|Proxy                     random(array $attributes = [])
 * @method static ProductPack|Proxy                     randomOrCreate(array $attributes = [])
 * @method static ProductPackRepository|RepositoryProxy repository()
 * @method static ProductPack[]|Proxy[]                 all()
 * @method static ProductPack[]|Proxy[]                 createMany(int $number, array|callable $attributes = [])
 * @method static ProductPack[]|Proxy[]                 createSequence(iterable|callable $sequence)
 * @method static ProductPack[]|Proxy[]                 findBy(array $attributes)
 * @method static ProductPack[]|Proxy[]                 randomRange(int $min, int $max, array $attributes = [])
 * @method static ProductPack[]|Proxy[]                 randomSet(int $number, array $attributes = [])
 */
final class ProductPackFactory extends ModelFactory
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
            'pack'=> PackMainFactory::new(),
            'product'=> ProductFactory::new(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(ProductPack $productPack): void {})
        ;
    }

    protected static function getClass(): string
    {
        return ProductPack::class;
    }
}
