<?php

namespace App\Repository;

use App\Entity\PriceListDetailed;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PriceListDetailed>
 *
 * @method PriceListDetailed|null find($id, $lockMode = null, $lockVersion = null)
 * @method PriceListDetailed|null findOneBy(array $criteria, array $orderBy = null)
 * @method PriceListDetailed[]    findAll()
 * @method PriceListDetailed[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PriceListDetailedRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PriceListDetailed::class);
    }


    public function createPriceListDetailed(PriceListDetailed $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOneBySkuAndPriceList(string $sku, $priceList): ?PriceListDetailed
    {
        return $this->createQueryBuilder('p')
            ->where('p.product = :val1')
            ->andWhere('p.priceList = :val2')
            ->setParameter('val1', $sku)
            ->setParameter('val2', $priceList)
            ->getQuery()
            ->getOneOrNullResult();
    }

//    /**
//     * @return PriceListDetailed[] Returns an array of PriceListDetailed objects
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

//    public function findOneBySomeField($value): ?PriceListDetailed
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
