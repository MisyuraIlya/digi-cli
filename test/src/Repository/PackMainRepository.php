<?php

namespace App\Repository;

use App\Entity\PackMain;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PackMain>
 *
 * @method PackMain|null find($id, $lockMode = null, $lockVersion = null)
 * @method PackMain|null findOneBy(array $criteria, array $orderBy = null)
 * @method PackMain[]    findAll()
 * @method PackMain[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PackMainRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PackMain::class);
    }

    public function findOneByExtIdAndQuantity(?string $extId, ?int $quantity): ?PackMain
    {
        return $this->createQueryBuilder('c')
            ->where('c.extId = :val1')
            ->andWhere('c.quantity = :val2')
            ->setParameter('val1', $extId)
            ->setParameter('val2', $quantity)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function save(PackMain $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return PackMain[] Returns an array of PackMain objects
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

//    public function findOneBySomeField($value): ?PackMain
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
