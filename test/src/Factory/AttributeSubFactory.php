<?php

namespace App\Factory;

use App\Entity\AttributeSub;
use App\Repository\AttributeSubRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<AttributeSub>
 *
 * @method        AttributeSub|Proxy                     create(array|callable $attributes = [])
 * @method static AttributeSub|Proxy                     createOne(array $attributes = [])
 * @method static AttributeSub|Proxy                     find(object|array|mixed $criteria)
 * @method static AttributeSub|Proxy                     findOrCreate(array $attributes)
 * @method static AttributeSub|Proxy                     first(string $sortedField = 'id')
 * @method static AttributeSub|Proxy                     last(string $sortedField = 'id')
 * @method static AttributeSub|Proxy                     random(array $attributes = [])
 * @method static AttributeSub|Proxy                     randomOrCreate(array $attributes = [])
 * @method static AttributeSubRepository|RepositoryProxy repository()
 * @method static AttributeSub[]|Proxy[]                 all()
 * @method static AttributeSub[]|Proxy[]                 createMany(int $number, array|callable $attributes = [])
 * @method static AttributeSub[]|Proxy[]                 createSequence(iterable|callable $sequence)
 * @method static AttributeSub[]|Proxy[]                 findBy(array $attributes)
 * @method static AttributeSub[]|Proxy[]                 randomRange(int $min, int $max, array $attributes = [])
 * @method static AttributeSub[]|Proxy[]                 randomSet(int $number, array $attributes = [])
 */
final class AttributeSubFactory extends ModelFactory
{

    private const TREASURE_NAMES = ['10v', '15v', '20v', '30v', '40v' , '50v', '60v'];

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
    protected function getDefaults(): array{
        return [
            'attribute' => AttributeMainFactory::new(),
            'title' =>  self::faker()->randomElement(self::TREASURE_NAMES),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(AttributeSub $attributeSub): void {})
        ;
    }

    protected static function getClass(): string
    {
        return AttributeSub::class;
    }
}
