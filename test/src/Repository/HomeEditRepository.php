<?php

namespace App\Repository;

use App\Entity\HomeEdit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HomeEdit>
 *
 * @method HomeEdit|null find($id, $lockMode = null, $lockVersion = null)
 * @method HomeEdit|null findOneBy(array $criteria, array $orderBy = null)
 * @method HomeEdit[]    findAll()
 * @method HomeEdit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HomeEditRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HomeEdit::class);
    }

    public function save(HomeEdit $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return HomeEdit[] Returns an array of HomeEdit objects
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

//    public function findOneBySomeField($value): ?HomeEdit
//    {
//        return $this->createQueryBuilder('h')
//            ->andWhere('h.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
