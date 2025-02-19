<?php

namespace App\Repository;

use App\Entity\Bonus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Bonus>
 *
 * @method Bonus|null find($id, $lockMode = null, $lockVersion = null)
 * @method Bonus|null findOneBy(array $criteria, array $orderBy = null)
 * @method Bonus[]    findAll()
 * @method Bonus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BonusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Bonus::class);
    }

    public function findBonusesByUserAndProduct(string $userExtId, int $productId): array
    {
        return $this->createQueryBuilder('b')
            ->leftJoin('b.bonusDetaileds', 'bd')
            ->addSelect('bd')
            ->leftJoin('bd.product', 'p')
            ->addSelect('p')
            ->where('b.userExtId = :userExtId')
            ->andWhere('p.id = :productId')
            ->andWhere('b.expiredAt IS NULL OR b.expiredAt > :currentDate')
            ->setParameter('userExtId', $userExtId)
            ->setParameter('productId', $productId)
            ->setParameter('currentDate', new \DateTimeImmutable())
            ->getQuery()
            ->getResult();
    }

    public function save(Bonus $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Bonus[] Returns an array of Bonus objects
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

//    public function findOneBySomeField($value): ?Bonus
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
