<?php

namespace App\Repository;

use App\Entity\AgentObjective;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use http\Encoding\Stream\Inflate;

/**
 * @extends ServiceEntityRepository<AgentObjective>
 *
 * @method AgentObjective|null find($id, $lockMode = null, $lockVersion = null)
 * @method AgentObjective|null findOneBy(array $criteria, array $orderBy = null)
 * @method AgentObjective[]    findAll()
 * @method AgentObjective[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AgentObjectiveRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AgentObjective::class);
    }

    public function calendarObjective($agentId, $weekFrom, $weekTo): array
    {
        $qb = $this->createQueryBuilder('ao');

        $missions = $qb
            ->select('ao', 'agent', 'client')
            ->leftJoin('ao.agent', 'agent')
            ->leftJoin('ao.client', 'client')
            ->andWhere('ao.agent = :agentId')
            ->setParameter('agentId', $agentId)
            ->andWhere($qb->expr()->gte('ao.date', ':weekFrom'))
            ->setParameter('weekFrom', new \DateTime($weekFrom))
            ->andWhere($qb->expr()->lte('ao.date', ':weekTo'))
            ->setParameter('weekTo', new \DateTime($weekTo))
            ->getQuery()
            ->getResult();

        return $missions;
    }

    public function findMissionsByAgentIdAndDateTimeRange($agentId, $weekFrom, $weekTo, $hourFrom, $hourTo)
    {
        $qb = $this->createQueryBuilder('ao');

        $missions = $qb
            ->andWhere('ao.agent = :agentId')
            ->setParameter('agentId', $agentId)
            ->andWhere($qb->expr()->gte('ao.date', ':weekFrom'))
            ->setParameter('weekFrom', new \DateTime($weekFrom))
            ->andWhere($qb->expr()->lte('ao.date', ':weekTo'))
            ->setParameter('weekTo', new \DateTime($weekTo))
            ->andWhere($qb->expr()->gte('ao.hourFrom', ':hourFrom'))
            ->setParameter('hourFrom', $hourFrom)
            ->andWhere($qb->expr()->lte('ao.hourTo', ':hourTo'))
            ->setParameter('hourTo', $hourTo)
            ->getQuery()
            ->getResult();

        return $missions;
    }

//    /**
//     * @return AgentObjective[] Returns an array of AgentObjective objects
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

//    public function findOneBySomeField($value): ?AgentObjective
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
