<?php

namespace App\Factory;

use App\Entity\ProductInfo;
use App\Repository\ProductInfoRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<ProductInfo>
 *
 * @method        ProductInfo|Proxy                     create(array|callable $attributes = [])
 * @method static ProductInfo|Proxy                     createOne(array $attributes = [])
 * @method static ProductInfo|Proxy                     find(object|array|mixed $criteria)
 * @method static ProductInfo|Proxy                     findOrCreate(array $attributes)
 * @method static ProductInfo|Proxy                     first(string $sortedField = 'id')
 * @method static ProductInfo|Proxy                     last(string $sortedField = 'id')
 * @method static ProductInfo|Proxy                     random(array $attributes = [])
 * @method static ProductInfo|Proxy                     randomOrCreate(array $attributes = [])
 * @method static ProductInfoRepository|RepositoryProxy repository()
 * @method static ProductInfo[]|Proxy[]                 all()
 * @method static ProductInfo[]|Proxy[]                 createMany(int $number, array|callable $attributes = [])
 * @method static ProductInfo[]|Proxy[]                 createSequence(iterable|callable $sequence)
 * @method static ProductInfo[]|Proxy[]                 findBy(array $attributes)
 * @method static ProductInfo[]|Proxy[]                 randomRange(int $min, int $max, array $attributes = [])
 * @method static ProductInfo[]|Proxy[]                 randomSet(int $number, array $attributes = [])
 */
final class ProductInfoFactory extends ModelFactory
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
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(ProductInfo $productInfo): void {})
        ;
    }

    protected static function getClass(): string
    {
        return ProductInfo::class;
    }
}
