<?php

namespace App\Factory;

use App\Entity\AttributeMain;
use App\Repository\AttributeMainRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<AttributeMain>
 *
 * @method        AttributeMain|Proxy                     create(array|callable $attributes = [])
 * @method static AttributeMain|Proxy                     createOne(array $attributes = [])
 * @method static AttributeMain|Proxy                     find(object|array|mixed $criteria)
 * @method static AttributeMain|Proxy                     findOrCreate(array $attributes)
 * @method static AttributeMain|Proxy                     first(string $sortedField = 'id')
 * @method static AttributeMain|Proxy                     last(string $sortedField = 'id')
 * @method static AttributeMain|Proxy                     random(array $attributes = [])
 * @method static AttributeMain|Proxy                     randomOrCreate(array $attributes = [])
 * @method static AttributeMainRepository|RepositoryProxy repository()
 * @method static AttributeMain[]|Proxy[]                 all()
 * @method static AttributeMain[]|Proxy[]                 createMany(int $number, array|callable $attributes = [])
 * @method static AttributeMain[]|Proxy[]                 createSequence(iterable|callable $sequence)
 * @method static AttributeMain[]|Proxy[]                 findBy(array $attributes)
 * @method static AttributeMain[]|Proxy[]                 randomRange(int $min, int $max, array $attributes = [])
 * @method static AttributeMain[]|Proxy[]                 randomSet(int $number, array $attributes = [])
 */
final class AttributeMainFactory extends ModelFactory
{
    private const TREASURE_NAMES = ['סוג', 'מידה', 'גובה', 'אורך', 'צבע' , 'טעם', 'ווט'];

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
            'extId' => self::faker()->numberBetween(1000, 1000000),
            'title' => self::faker()->randomElement(self::TREASURE_NAMES),
            'isInFilter' => self::faker()->boolean(),
            'isInProductCard' => self::faker()->boolean(),
            'isPublished' => self::faker()->boolean(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(AttributeMain $attributeMain): void {})
        ;
    }

    protected static function getClass(): string
    {
        return AttributeMain::class;
    }
}
