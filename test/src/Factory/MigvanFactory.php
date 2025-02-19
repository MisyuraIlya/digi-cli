<?php

namespace App\Factory;

use App\Entity\Migvan;
use App\Repository\MigvanRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<Migvan>
 *
 * @method        Migvan|Proxy                     create(array|callable $attributes = [])
 * @method static Migvan|Proxy                     createOne(array $attributes = [])
 * @method static Migvan|Proxy                     find(object|array|mixed $criteria)
 * @method static Migvan|Proxy                     findOrCreate(array $attributes)
 * @method static Migvan|Proxy                     first(string $sortedField = 'id')
 * @method static Migvan|Proxy                     last(string $sortedField = 'id')
 * @method static Migvan|Proxy                     random(array $attributes = [])
 * @method static Migvan|Proxy                     randomOrCreate(array $attributes = [])
 * @method static MigvanRepository|RepositoryProxy repository()
 * @method static Migvan[]|Proxy[]                 all()
 * @method static Migvan[]|Proxy[]                 createMany(int $number, array|callable $attributes = [])
 * @method static Migvan[]|Proxy[]                 createSequence(iterable|callable $sequence)
 * @method static Migvan[]|Proxy[]                 findBy(array $attributes)
 * @method static Migvan[]|Proxy[]                 randomRange(int $min, int $max, array $attributes = [])
 * @method static Migvan[]|Proxy[]                 randomSet(int $number, array $attributes = [])
 */
final class MigvanFactory extends ModelFactory
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
            'sku' => ProductFactory::new(),
            'createdAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'isPublished' => self::faker()->boolean(),
            'updatedAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(Migvan $migvan): void {})
        ;
    }

    protected static function getClass(): string
    {
        return Migvan::class;
    }
}
