<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Category>
 *
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        UserRepository $userRepository,
    )
    {
        $this->userRepository = $userRepository;
        parent::__construct($registry, Category::class);
    }

    public function createCategory(Category $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOneByExtId(?string $extId): ?Category
    {
        return $this->createQueryBuilder('c')
            ->where('c.extId = :val1')
            ->setParameter('val1', $extId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneByExtIdAndIdentify(?string $extId, ?string $identify): ?Category
    {
        return $this->createQueryBuilder('c')
            ->where('c.extId = :val1')
            ->andWhere('c.identify = :val2')
            ->setParameter('val1', $extId)
            ->setParameter('val2', $identify)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAllCategoryLvl1(): Array
    {
        return $this->createQueryBuilder('c')
            ->where('c.lvlNumber = 1')
            ->getQuery()
            ->getResult();
    }

    public function findOneByExtIdAndParentId(?string $extId, $parentId): ?Category
    {
        return $this->createQueryBuilder('c')
            ->where('c.extId = :val1')
            ->andWhere('c.parent = :val2')
            ->setParameter('val1', $extId)
            ->setParameter('val2', $parentId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneByExtIdAndLvlNumber(?string $extId, int $lvl): ?Category
    {
        return $this->createQueryBuilder('c')
            ->where('c.extId = :val1')
            ->andWhere('c.lvlNumber = :val2')
            ->setParameter('val1', $extId)
            ->setParameter('val2', $lvl)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneByTitle(?string $title): ?Category
    {
        return $this->createQueryBuilder('c')
            ->where('c.title = :val1')
            ->setParameter('val1', $title)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getCategoriesByMigvanAndSearch(?string $searchValue)
    {
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder->select('p')
            ->from(Product::class, 'p')
            ->andWhere('p.isPublished = true');


        if ($searchValue) {
            $queryBuilder->andWhere($queryBuilder->expr()->like('p.title', ':searchValue'));
            $queryBuilder->setParameter('searchValue', '%' . $searchValue . '%');
        }

        $products = $queryBuilder->getQuery()->getResult();
        $prods = [];
        $categoriesLvl2 = [];
        $categoriesLvl3 = [];
        foreach ($products as $product) {
            assert($product instanceof Product);
            if($product->getCategoryLvl2()){
                $categoriesLvl2[] = $product->getCategoryLvl2()->getId();
            }
            if($product->getCategoryLvl3()){
                $categoriesLvl3[] = $product->getCategoryLvl3()->getId();
            }
            $prods[] = $product->getId();
        }
        $qb = $this->createQueryBuilder('c');
        $qb->join('c.productsLvl1', 'p')
            ->where($qb->expr()->in('p.id', ':productIds'))
            ->setParameter('productIds', $prods);

        $result =  $qb->getQuery()->getResult();
        foreach ($result as $itemRec){
            assert($itemRec instanceof Category);
            $newCat2 = [];
            foreach ($itemRec->getCategories()->toArray() as $subCat) {
                assert($subCat instanceof Category);
                $newCat3 = [];
                if(in_array($subCat->getId(), $categoriesLvl2)){
                    $newCat2[] = $subCat;
                    foreach ($subCat->getCategories() as $subCat3) {
                        assert($subCat3 instanceof Category);
                        if(in_array($subCat3->getId(), $categoriesLvl3)){
                            $newCat3[] = $subCat3;
                            $subCat->removeCategory($subCat3);
                        }
                    }
                }
                $itemRec->removeCategory($subCat);
                $subCat->setCategories($newCat3);
            }
            $itemRec->setCategories($newCat2);
        }


        return $result;
    }

    public function GetAppCategories()
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.categories', 'subcategories', 'WITH', 'subcategories.isPublished = true')
            ->addSelect('subcategories')
            ->where('c.lvlNumber = 1')
            ->andWhere('c.isPublished = true')
            ->orderBy('c.orden', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function GetAdminCategories(string $lvl1, string $lvl2)
    {

        $qb = $this->createQueryBuilder('c');
        if ($lvl1 !== '0' && $lvl2 == '0') {
            $qb->andWhere('c.parent = :lvl1')
                ->setParameter('lvl1', $lvl1);
        } elseif ($lvl1 !== '0' && $lvl2 !== '0') {
            $qb->orWhere('c.parent = :lvl2')
                ->setParameter('lvl2', $lvl2);
        }  else {
            $qb->andWhere('c.lvlNumber = 1');
        }
        return $qb->orderBy('c.orden', 'ASC')->getQuery()->getResult();
    }

}
