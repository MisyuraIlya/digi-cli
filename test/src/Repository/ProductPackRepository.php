<?php

namespace App\Repository;

use App\Entity\ProductPack;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductPack>
 *
 * @method ProductPack|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductPack|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductPack[]    findAll()
 * @method ProductPack[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductPackRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductPack::class);
    }

    public function findOneByProductIdAndPackId(?string $product, ?string $pack): ?ProductPack
    {
        return $this->createQueryBuilder('p')
            ->where('p.product = :val1')
            ->andWhere('p.pack = :val2')
            ->setParameter('val1', $product)
            ->setParameter('val2', $pack)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function save(ProductPack $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return ProductPack[] Returns an array of ProductPack objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ProductPack
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
