<?php

namespace App\Factory;

use App\Entity\HistoryDetailed;
use App\Repository\HistoryDetailedRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<HistoryDetailed>
 *
 * @method        HistoryDetailed|Proxy                     create(array|callable $attributes = [])
 * @method static HistoryDetailed|Proxy                     createOne(array $attributes = [])
 * @method static HistoryDetailed|Proxy                     find(object|array|mixed $criteria)
 * @method static HistoryDetailed|Proxy                     findOrCreate(array $attributes)
 * @method static HistoryDetailed|Proxy                     first(string $sortedField = 'id')
 * @method static HistoryDetailed|Proxy                     last(string $sortedField = 'id')
 * @method static HistoryDetailed|Proxy                     random(array $attributes = [])
 * @method static HistoryDetailed|Proxy                     randomOrCreate(array $attributes = [])
 * @method static HistoryDetailedRepository|RepositoryProxy repository()
 * @method static HistoryDetailed[]|Proxy[]                 all()
 * @method static HistoryDetailed[]|Proxy[]                 createMany(int $number, array|callable $attributes = [])
 * @method static HistoryDetailed[]|Proxy[]                 createSequence(iterable|callable $sequence)
 * @method static HistoryDetailed[]|Proxy[]                 findBy(array $attributes)
 * @method static HistoryDetailed[]|Proxy[]                 randomRange(int $min, int $max, array $attributes = [])
 * @method static HistoryDetailed[]|Proxy[]                 randomSet(int $number, array $attributes = [])
 */
final class HistoryDetailedFactory extends ModelFactory
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
            'product' => ProductFactory::new(),
            'history' => HistoryFactory::new(),
            'singlePrice' => self::faker()->numberBetween(10, 1000),
            'quantity' => self::faker()->numberBetween(1, 100),
            'discount' => self::faker()->numberBetween(0, 100),
            'total' => self::faker()->numberBetween(100, 10000),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(HistoryDetailed $historyDetailed): void {})
        ;
    }

    protected static function getClass(): string
    {
        return HistoryDetailed::class;
    }
}
