<?php

namespace App\Repository;

use App\Entity\Migvan;
use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use ApiPlatform\Doctrine\Orm\Paginator;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;

/**
 * @extends ServiceEntityRepository<Product>
 *
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    const ITEMS_PER_PAGE = 2;

    public function __construct(
        ManagerRegistry $registry,
        UserRepository $userRepository,
    )
    {
        $this->userRepository = $userRepository;
        parent::__construct($registry, Product::class);
    }

    public function createProduct(Product $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOneBySku(string $sku): ?Product
    {
        return $this->createQueryBuilder('c')
            ->where('c.sku = :val1')
            ->setParameter('val1', $sku)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneBySkuAndToArray(string $sku): array
    {
        return $this->createQueryBuilder('p')
            ->select(['p', 'packProducts', 'packMain', 'imagePath'])
            ->leftJoin('p.packProducts', 'packProducts')
            ->leftJoin('packProducts.pack', 'packMain')
            ->leftJoin('p.imagePath', 'imagePath')
            ->where('p.sku = :val1')
            ->setParameter('val1', $sku)
            ->getQuery()
            ->getArrayResult();
    }


    public function findOneBySkuAndIdentify(string $sku, string $identify): ?Product
    {
        return $this->createQueryBuilder('c')
            ->where('c.sku = :val1')
            ->andWhere('c.identify = :val2')
            ->setParameter('val1', $sku)
            ->setParameter('val2', $identify)
            ->getQuery()
            ->getOneOrNullResult();
    }
    public function getAllCatalog(
        int $lvl1 = null,
        int $lvl2 = null,
        int $lvl3 = null,
        bool $showAll = false,
        ?string $orderBy = null,
        ?array $filters = null,
        ?string $searchValue = null,
        ?array $makatsForSearch = null
    ): array {
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder->select('p', 'c1', 'c2', 'c3')
            ->from(Product::class, 'p')
            ->leftJoin('p.categoryLvl1', 'c1')->addSelect('c1')
            ->leftJoin('p.categoryLvl2', 'c2')->addSelect('c2')
            ->leftJoin('p.categoryLvl3', 'c3')->addSelect('c3');

        if (!$showAll) {
            $queryBuilder->andWhere("p.isPublished = :isPublished")
                ->setParameter('isPublished', true);
        }

        if (!empty($makatsForSearch)) {
            $queryBuilder->andWhere($queryBuilder->expr()->in('p.sku', ':makatsForSearch'))
                ->setParameter('makatsForSearch', $makatsForSearch);
        }

        if ($lvl1) {
            $queryBuilder->andWhere('p.categoryLvl1 = :lvl1')
                ->setParameter('lvl1', $lvl1);
        }

        if ($lvl2) {
            $queryBuilder->andWhere('p.categoryLvl2 = :lvl2')
                ->setParameter('lvl2', $lvl2);
        }

        if ($lvl3) {
            $queryBuilder->andWhere('p.categoryLvl3 = :lvl3')
                ->setParameter('lvl3', $lvl3);
        }

        // Apply filters
        if (!empty($filters)) {
            foreach ($filters as $attributeSubId => $values) {
                if (!is_array($values)) {
                    $values = [$values]; // Ensure values are an array
                }

                // Join product attributes and filter by attributeSub
                $alias = 'pa' . $attributeSubId;
                $queryBuilder->join('p.productAttributes', $alias)
                    ->andWhere($queryBuilder->expr()->in("$alias.attributeSub", ":values$attributeSubId"))
                    ->setParameter("values$attributeSubId", $values);
            }
        }

        if ($searchValue !== null) {
            $titleCondition = $queryBuilder->expr()->like('p.title', ':searchValueTitle');
            $skuCondition = $queryBuilder->expr()->like('p.sku', ':searchValueSku');

            $queryBuilder->andWhere($queryBuilder->expr()->orX($titleCondition, $skuCondition));

            $queryBuilder->setParameter('searchValueTitle', '%' . $searchValue . '%');
            $queryBuilder->setParameter('searchValueSku', '%' . $searchValue . '%');
        }

        if ($orderBy !== null) {
            $queryBuilder->orderBy("p.$orderBy", 'ASC');
        }

        $query = $queryBuilder->getQuery();

        return $query->getResult(); // Fetch all results without pagination
    }


    public function getCatalog(
        int $page = 1,
        int $itemsPerPage = 24,
        int $lvl1 = null,
        int $lvl2 = null,
        int $lvl3 = null,
        bool $showAll = false,
        ?string $orderBy = null,
        ?array $filters = null,
        ?string $searchValue = null,
        ?array $makatsForSearch = null,
    ): Paginator {
        $firstResult = ($page - 1) * $itemsPerPage;
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder->select('p', 'c1', 'c2', 'c3')
            ->from(Product::class, 'p')
            ->leftJoin('p.categoryLvl1', 'c1')->addSelect('c1')
            ->leftJoin('p.categoryLvl2', 'c2')->addSelect('c2')
            ->leftJoin('p.categoryLvl3', 'c3')->addSelect('c3');

        if (!$showAll) {
            $queryBuilder->andWhere("p.isPublished = :isPublished")
                ->setParameter('isPublished', true);
        }

        if (!empty($makatsForSearch)) {
            $queryBuilder->andWhere($queryBuilder->expr()->in('p.sku', ':makatsForSearch'))
                ->setParameter('makatsForSearch', $makatsForSearch);
        }

        if ($lvl1) {
            $queryBuilder->andWhere('p.categoryLvl1 = :lvl1')
                ->setParameter('lvl1', $lvl1);
        }

        if ($lvl2) {
            $queryBuilder->andWhere('p.categoryLvl2 = :lvl2')
                ->setParameter('lvl2', $lvl2);
        }

        if ($lvl3) {
            $queryBuilder->andWhere('p.categoryLvl3 = :lvl3')
                ->setParameter('lvl3', $lvl3);
        }

        // Apply filters
        if (!empty($filters)) {
            foreach ($filters as $attributeSubId => $values) {
                if (!is_array($values)) {
                    $values = [$values]; // Ensure values are an array
                }

                // Join product attributes and filter by attributeSub
                $alias = 'pa' . $attributeSubId;
                $queryBuilder->join('p.productAttributes', $alias)
                    ->andWhere($queryBuilder->expr()->in("$alias.attributeSub", ":values$attributeSubId"))
                    ->setParameter("values$attributeSubId", $values);
            }
        }

        if ($searchValue !== null) {
            $titleCondition = $queryBuilder->expr()->like('p.title', ':searchValueTitle');
            $skuCondition = $queryBuilder->expr()->like('p.sku', ':searchValueSku');

            $queryBuilder->andWhere($queryBuilder->expr()->orX($titleCondition, $skuCondition));

            $queryBuilder->setParameter('searchValueTitle', '%' . $searchValue . '%');
            $queryBuilder->setParameter('searchValueSku', '%' . $searchValue . '%');
        }

        if ($orderBy !== null) {
            $queryBuilder->orderBy("p.$orderBy", 'ASC');
        }

        $query = $queryBuilder->getQuery()
            ->setFirstResult($firstResult)
            ->setMaxResults($itemsPerPage);

        $doctrinePaginator = new DoctrinePaginator($query);
        $paginator = new Paginator($doctrinePaginator);

        return $paginator;
    }



    public function GetAllProducts()
    {
        return $this->createQueryBuilder('p')
            ->getQuery()
            ->getArrayResult();
    }

    public function getSpecialProducts()
    {
        return $this->createQueryBuilder('p')
            ->where('p.isSpecial = true')
            ->getQuery()
            ->getResult();
    }

    public function getNewProducts()
    {
        return $this->createQueryBuilder('p')
            ->where('p.isNew = true')
            ->getQuery()
            ->getResult();
    }

}
