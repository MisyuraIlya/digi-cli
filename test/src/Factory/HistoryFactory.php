<?php

namespace App\Factory;

use App\Entity\History;
use App\Enum\PurchaseStatus;
use App\Repository\HistoryRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;
/**
 * @extends ModelFactory<History>
 *
 * @method        History|Proxy                     create(array|callable $attributes = [])
 * @method static History|Proxy                     createOne(array $attributes = [])
 * @method static History|Proxy                     find(object|array|mixed $criteria)
 * @method static History|Proxy                     findOrCreate(array $attributes)
 * @method static History|Proxy                     first(string $sortedField = 'id')
 * @method static History|Proxy                     last(string $sortedField = 'id')
 * @method static History|Proxy                     random(array $attributes = [])
 * @method static History|Proxy                     randomOrCreate(array $attributes = [])
 * @method static HistoryRepository|RepositoryProxy repository()
 * @method static History[]|Proxy[]                 all()
 * @method static History[]|Proxy[]                 createMany(int $number, array|callable $attributes = [])
 * @method static History[]|Proxy[]                 createSequence(iterable|callable $sequence)
 * @method static History[]|Proxy[]                 findBy(array $attributes)
 * @method static History[]|Proxy[]                 randomRange(int $min, int $max, array $attributes = [])
 * @method static History[]|Proxy[]                 randomSet(int $number, array $attributes = [])
 */
final class HistoryFactory extends ModelFactory
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
        $purchaseStatusValues = (new \ReflectionClass(PurchaseStatus::class))->getConstants();
        return [
            'user' => UserFactory::new(),
            'orderExtId' =>  self::faker()->numberBetween(100000, 999999),
            'deliveryDate' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'deliveryPrice' => self::faker()->numberBetween(100, 1000),
            'isBuyByCreditCard' => self::faker()->boolean(),
            'isSendErp' => self::faker()->boolean(),
            'sendErpAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'discount' => self::faker()->numberBetween(0, 100),
            'total' => self::faker()->numberBetween(0, 100000),
            'orderComment' => self::faker()->text(255),
            'orderStatus' => self::faker()->randomElement($purchaseStatusValues),
            'createdAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'updatedAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(History $history): void {})
        ;
    }

    protected static function getClass(): string
    {
        return History::class;
    }
}
