<?php

namespace App\Factory;

use App\Entity\ProductImages;
use App\Repository\ProductImagesRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<ProductImages>
 *
 * @method        ProductImages|Proxy                     create(array|callable $attributes = [])
 * @method static ProductImages|Proxy                     createOne(array $attributes = [])
 * @method static ProductImages|Proxy                     find(object|array|mixed $criteria)
 * @method static ProductImages|Proxy                     findOrCreate(array $attributes)
 * @method static ProductImages|Proxy                     first(string $sortedField = 'id')
 * @method static ProductImages|Proxy                     last(string $sortedField = 'id')
 * @method static ProductImages|Proxy                     random(array $attributes = [])
 * @method static ProductImages|Proxy                     randomOrCreate(array $attributes = [])
 * @method static ProductImagesRepository|RepositoryProxy repository()
 * @method static ProductImages[]|Proxy[]                 all()
 * @method static ProductImages[]|Proxy[]                 createMany(int $number, array|callable $attributes = [])
 * @method static ProductImages[]|Proxy[]                 createSequence(iterable|callable $sequence)
 * @method static ProductImages[]|Proxy[]                 findBy(array $attributes)
 * @method static ProductImages[]|Proxy[]                 randomRange(int $min, int $max, array $attributes = [])
 * @method static ProductImages[]|Proxy[]                 randomSet(int $number, array $attributes = [])
 */
final class ProductImagesFactory extends ModelFactory
{
    private const TREASURE_NAMES = [
        'https://konimboimages.s3.amazonaws.com/system/photos/1528915/original/ec5c5b712fb609432f55dc585b1107e6.jpg',
        'https://konimboimages.s3.amazonaws.com/system/photos/1528917/original/79560000c5451b6141ff264128c7c811.jpg',
        'https://konimboimages.s3.amazonaws.com/system/photos/1528941/original/571a8d06ec535887e684391001b4ac9c.jpg',
        'http://www.argentools.co.il/items/1032535-פנס-DML812-18V-Makita-?source=google-remarketing',
        'http://www.argentools.co.il/items/1032536-חותך-זכוכית-CC301DZJ-12V-Makita-?source=google-remarketing',
        'http://www.argentools.co.il/items/1032537-מספרי-פח-DJS161-18V-Makita-?source=google-remarketing',
        'http://www.argentools.co.il/items/1032543-כרסם-גבס-DCO180-18V-Makita-?source=google-remarketing',
        'http://www.argentools.co.il/items/1032542-אקדח-מרק-DCG180ZB-18V-Makita-?source=google-remarketing',
        'http://www.argentools.co.il/items/1032541-מחבר-ביסקוויטים-DPJ180-18V-Makita-?source=google-remarketing',
        'http://www.argentools.co.il/items/1032537-מספרי-פח-DJS161-18V-Makita-?source=google-remarketing',
        'http://www.argentools.co.il/items/1032593-מברגה-לברגי-גבס-FS4000-Makita-?source=google-remarketing',
        'http://www.argentools.co.il/items/1032594-מברגה-לברגי-גבס-FS6300-Makita-?source=google-remarketing',
        'http://www.argentools.co.il/items/1032595-מברגה-לברגי-איסכורית-FS2700-Makita-?source=google-remarketing',
        'http://www.argentools.co.il/items/1032600-משחזת-זווית-GA4530-Makita-?source=google-remarketing',
        'http://www.argentools.co.il/items/1032604-משחזת-זווית-9565CVR-Makita-?source=google-remarketing',
        'http://www.argentools.co.il/items/1032605-משחזת-זווית-GA6010-Makita-?source=google-remarketing',
        'http://www.argentools.co.il/items/1032608-משחזת-זווית-GA9050-Makita-?source=google-remarketing',
        'http://www.argentools.co.il/items/1032611-משחזת-ציר-1-4-GD0800C-Makita-?source=google-remarketing',
        'http://www.argentools.co.il/items/1032622-מלטשת-אקצנטרית-BO6030-Makita-?source=google-remarketing',
        'http://www.argentools.co.il/items/1032625-מלטשת-סרט-9911-Makita-?source=google-remarketing',
        'http://www.argentools.co.il/items/1032626-מלטשת-סרט-9404-Makita-?source=google-remarketing',

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
            'product' => ProductFactory::new(),
            'createdAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'imagePath' => self::faker()->randomElement(self::TREASURE_NAMES),
            'updatedAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(ProductImages $productImages): void {})
        ;
    }

    protected static function getClass(): string
    {
        return ProductImages::class;
    }
}
