<?php

namespace App\Repository;

use App\Entity\BonusDetailed;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BonusDetailed>
 *
 * @method BonusDetailed|null find($id, $lockMode = null, $lockVersion = null)
 * @method BonusDetailed|null findOneBy(array $criteria, array $orderBy = null)
 * @method BonusDetailed[]    findAll()
 * @method BonusDetailed[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BonusDetailedRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BonusDetailed::class);
    }

    public function save(BonusDetailed $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOneByIds(int $bonusId, int $productId, int $bonusProductId): ?BonusDetailed
    {
        return $this->createQueryBuilder('bd')
            ->andWhere('bd.bonus = :bonusId')
            ->andWhere('bd.product = :productId')
            ->andWhere('bd.bonusProduct = :bonusProductId')
            ->setParameters([
                'bonusId' => $bonusId,
                'productId' => $productId,
                'bonusProductId' => $bonusProductId,
            ])
            ->getQuery()
            ->getOneOrNullResult();
    }


//    /**
//     * @return BonusDetailed[] Returns an array of BonusDetailed objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('b.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?BonusDetailed
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
