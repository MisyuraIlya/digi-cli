<?php

namespace App\Factory;

use App\Entity\PackMain;
use App\Repository\PackMainRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<PackMain>
 *
 * @method        PackMain|Proxy                     create(array|callable $attributes = [])
 * @method static PackMain|Proxy                     createOne(array $attributes = [])
 * @method static PackMain|Proxy                     find(object|array|mixed $criteria)
 * @method static PackMain|Proxy                     findOrCreate(array $attributes)
 * @method static PackMain|Proxy                     first(string $sortedField = 'id')
 * @method static PackMain|Proxy                     last(string $sortedField = 'id')
 * @method static PackMain|Proxy                     random(array $attributes = [])
 * @method static PackMain|Proxy                     randomOrCreate(array $attributes = [])
 * @method static PackMainRepository|RepositoryProxy repository()
 * @method static PackMain[]|Proxy[]                 all()
 * @method static PackMain[]|Proxy[]                 createMany(int $number, array|callable $attributes = [])
 * @method static PackMain[]|Proxy[]                 createSequence(iterable|callable $sequence)
 * @method static PackMain[]|Proxy[]                 findBy(array $attributes)
 * @method static PackMain[]|Proxy[]                 randomRange(int $min, int $max, array $attributes = [])
 * @method static PackMain[]|Proxy[]                 randomSet(int $number, array $attributes = [])
 */
final class PackMainFactory extends ModelFactory
{
    private const TREASURE_NAMES = [
        'יחידה',
        'קופסה',
        'אריזה',
        'משטח'
    ];

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
            'extId' => self::faker()->numberBetween(1000000, 9999999),
            'name' => self::faker()->randomElement(self::TREASURE_NAMES),
            'quantity' => self::faker()->numberBetween(1, 500),
            'barcode' => self::faker()->numberBetween(100000, 999999),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(PackMain $packMain): void {})
        ;
    }

    protected static function getClass(): string
    {
        return PackMain::class;
    }
}
