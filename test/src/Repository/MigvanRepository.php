<?php

namespace App\Repository;

use App\Entity\Migvan;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Migvan>
 *
 * @method Migvan|null find($id, $lockMode = null, $lockVersion = null)
 * @method Migvan|null findOneBy(array $criteria, array $orderBy = null)
 * @method Migvan[]    findAll()
 * @method Migvan[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MigvanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Migvan::class);
    }

    public function createMigvan(Migvan $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOneBySkuAndUserExtId(?string $sku, ?string $userExtId): ?Migvan
    {
        return $this->createQueryBuilder('m')
            ->where('m.sku = :val1')
            ->andWhere('m.user = :val2')
            ->setParameter('val1', $sku)
            ->setParameter('val2', $userExtId)
            ->getQuery()
            ->getOneOrNullResult();
    }

//    /**
//     * @return Migvan[] Returns an array of Migvan objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Migvan
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
