<?php

namespace App\Repository;

use App\Entity\AttributeMain;
use App\Entity\Category;
use App\Entity\Product;
use App\Entity\ProductAttribute;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\AttributeSub;

/**
 * @extends ServiceEntityRepository<AttributeMain>
 *
 * @method AttributeMain|null find($id, $lockMode = null, $lockVersion = null)
 * @method AttributeMain|null findOneBy(array $criteria, array $orderBy = null)
 * @method AttributeMain[]    findAll()
 * @method AttributeMain[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AttributeMainRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AttributeMain::class);
    }

    public function createAttributeMain(AttributeMain $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOneByExtId(string $extId): ?AttributeMain
    {
        return $this->createQueryBuilder('a')
            ->where('a.extId = :val1')
            ->setParameter('val1', $extId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneByExtIdAndTitle(?string $extId, ?string $title): ?AttributeMain
    {
        return $this->createQueryBuilder('a')
            ->where('a.extId = :val1')
            ->where('a.title = :val2')
            ->setParameter('val1', $extId)
            ->setParameter('val2', $title)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAttributesByCategoryExistProducts(int $lvl1, int $lvl2, int $lvl3, ?string $userExtId, ?array $migvanOnline,?string $searchValue)
    {
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder->select('p')
            ->from(Product::class, 'p')
            ->where('p.isPublished = true');

        if (!empty($migvanOnline)) {
            $queryBuilder->andWhere($queryBuilder->expr()->in('p.sku', $migvanOnline));
        }

        if ($userExtId && empty($migvanOnline)) {
            $user = $this->userRepository->findOneByExtId($userExtId);
            $queryBuilder->join('p.migvans', 'm')
                ->where('m.user = :user')
                ->setParameter('user', $user);
        }

        if ($searchValue) {
            $queryBuilder->andWhere($queryBuilder->expr()->like('p.title', ':searchValue'));
            $queryBuilder->setParameter('searchValue', '%' . $searchValue . '%');
        }

        if ($lvl1) {
            $queryBuilder->andWhere('p.categoryLvl1 = :lvl1')
                ->setParameter('lvl1', $lvl1);
            if ($lvl2) {
                $queryBuilder->andWhere('p.categoryLvl2 = :lvl2')
                    ->setParameter('lvl2', $lvl2);
                if ($lvl3) {
                    $queryBuilder->andWhere('p.categoryLvl3 = :lvl3')
                        ->setParameter('lvl3', $lvl3);
                }
            }
        }
        $products = $queryBuilder->getQuery()->getResult();
        $prods = [];
        foreach ($products as $product) {
            $prods[] = $product->getId();
        }
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder->select('pa')
            ->from(ProductAttribute::class, 'pa')
            ->where($queryBuilder->expr()->in('pa.product', ':products'))
            ->setParameter('products', $prods);

        $productAttributes = $queryBuilder->getQuery()->getResult();
        $subAttributes = [];
        foreach ($productAttributes as $subAttribute) {
            $subAttributes[] = ($subAttribute->getAttributeSub())->getId();
        }
        $uniqueArray2 = array_unique($subAttributes);
        $uniqueArray2 = array_unique($uniqueArray2);
        $res = $this->fetchByArr($uniqueArray2);
        return $res;
    }

    public function findAttributesBySkus(array $skus)
    {
        if (empty($skus)) {
            return []; // Return empty if no SKUs provided
        }

        // Fetch products based on SKUs and count product occurrences
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder->select('IDENTITY(pa.attributeSub) as subAttributeId, COUNT(p.id) as productCount')
            ->from(ProductAttribute::class, 'pa')
            ->join('pa.product', 'p')
            ->where($queryBuilder->expr()->in('p.sku', ':skus'))
            ->setParameter('skus', $skus)
            ->groupBy('pa.attributeSub');

        $productCounts = $queryBuilder->getQuery()->getResult();
        // Create a map of product counts for SubAttribute IDs
        $productCountMap = [];
        foreach ($productCounts as $result) {
            $productCountMap[$result['subAttributeId']] = $result['productCount'];
        }

        // Fetch main attributes and their sub-attributes
        $uniqueArray2 = array_keys($productCountMap);
        $attributes = $this->fetchByArr($uniqueArray2);

        // Inject productCount into the result
        foreach ($attributes as &$attribute) {
            foreach ($attribute->getSubAttributes() as &$subAttribute) {
                $subAttributeId = $subAttribute->getId();
                $subAttribute->setProductCount($productCountMap[$subAttributeId] ?? 0);
            }
        }
        return $attributes;
    }

    private function fetchByArr($uniqueArray2)
    {
        $dql = "SELECT am, sa 
            FROM App\Entity\AttributeMain am
            LEFT JOIN am.SubAttributes sa
            WHERE sa.id IN (:uniqueArray2)
            ORDER BY am.orden ASC";

        return $this->_em->createQuery($dql)
            ->setParameter('uniqueArray2', $uniqueArray2)
            ->getResult();
    }


}
