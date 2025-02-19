<?php

namespace App\Repository;

use App\Entity\HomeMedia;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HomeMedia>
 *
 * @method HomeMedia|null find($id, $lockMode = null, $lockVersion = null)
 * @method HomeMedia|null findOneBy(array $criteria, array $orderBy = null)
 * @method HomeMedia[]    findAll()
 * @method HomeMedia[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HomeMediaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HomeMedia::class);
    }

//    /**
//     * @return HomeMedia[] Returns an array of HomeMedia objects
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

//    public function findOneBySomeField($value): ?HomeMedia
//    {
//        return $this->createQueryBuilder('h')
//            ->andWhere('h.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
