<?php

namespace App\Repository;

use App\Entity\AttributeSub;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AttributeSub>
 *
 * @method AttributeSub|null find($id, $lockMode = null, $lockVersion = null)
 * @method AttributeSub|null findOneBy(array $criteria, array $orderBy = null)
 * @method AttributeSub[]    findAll()
 * @method AttributeSub[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AttributeSubRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AttributeSub::class);
    }

    public function createSubAttribute(AttributeSub $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOneBySkuAndAttributeMain(string $sku, string $attribute): ?AttributeSub
    {
        return $this->createQueryBuilder('a')
            ->where('a.product = :val1')
            ->andWhere('a.attribute = :val2')
            ->setParameter('val1', $sku)
            ->setParameter('val2', $attribute)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneByTitle( ?string $title): ?AttributeSub
    {
        return $this->createQueryBuilder('a')
            ->where('a.title = :val1')
            ->setParameter('val1', $title)
            ->getQuery()
            ->getOneOrNullResult();
    }

//    /**
//     * @return AttributeSub[] Returns an array of AttributeSub objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?AttributeSub
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
