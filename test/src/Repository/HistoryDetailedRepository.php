<?php

namespace App\Repository;

use App\Entity\HistoryDetailed;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HistoryDetailed>
 *
 * @method HistoryDetailed|null find($id, $lockMode = null, $lockVersion = null)
 * @method HistoryDetailed|null findOneBy(array $criteria, array $orderBy = null)
 * @method HistoryDetailed[]    findAll()
 * @method HistoryDetailed[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HistoryDetailedRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HistoryDetailed::class);
    }

    public function createHistoryDetailed(HistoryDetailed $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOneByHistoryId(int $id): array
    {
        return $this->createQueryBuilder('hd')
            ->where('hd.history = :val1')
            ->setParameter('val1', $id)
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return HistoryDetailed[] Returns an array of HistoryDetailed objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('h')
//            ->andWhere('h.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('h.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?HistoryDetailed
//    {
//        return $this->createQueryBuilder('h')
//            ->andWhere('h.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
