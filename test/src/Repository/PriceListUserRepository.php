<?php

namespace App\Repository;

use App\Entity\PriceListUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PriceListUser>
 *
 * @method PriceListUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method PriceListUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method PriceListUser[]    findAll()
 * @method PriceListUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PriceListUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PriceListUser::class);
    }

    public function findOneByUserIdAndPriceListId(?string $userId, ?string $priceListId): ?PriceListUser
    {
        return $this->createQueryBuilder('p')
            ->where('p.user = :val1')
            ->andWhere('p.priceList = :val2')
            ->setParameter('val1', $userId)
            ->setParameter('val2', $priceListId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function save(PriceListUser $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return PriceListUser[] Returns an array of PriceListUser objects
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

//    public function findOneBySomeField($value): ?PriceListUser
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
