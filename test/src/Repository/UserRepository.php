<?php

namespace App\Repository;

use ApiPlatform\Doctrine\Orm\Paginator;
use App\Entity\User;
use App\Enum\UsersTypes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @implements PasswordUpgraderInterface<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }


    public function createUser(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOneByExIdAndPhone($exId, $phone): ?User
    {
        return $this->createQueryBuilder('u')
            ->where('u.extId = :val1')
            ->andWhere('u.phone = :val2')
            ->setParameter('val1', $exId)
            ->setParameter('val2', $phone)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneByEmail($email): ?User
    {
        return $this->createQueryBuilder('u')
            ->where('u.email = :val1')
            ->setParameter('val1', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneByExtId($extId): ?User
    {
        return $this->createQueryBuilder('u')
            ->where('u.extId = :val1')
            ->setParameter('val1', $extId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneByExtIdAndAgent($extId): ?User
    {
        return $this->createQueryBuilder('u')
            ->where('u.extId = :extId')
            ->andWhere('u.role = :role')
            ->setParameter('extId', $extId)
            ->setParameter('role', UsersTypes::AGENT)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findParentUser(?string $id): ?User
    {
        if(!$id){
            return null;
        }
        $result = $this->createQueryBuilder('u')
            ->where('u.id = :val1')
            ->setParameter('val1', $id)
            ->getQuery()
            ->getResult();

        if (is_array($result)) {
            return $result[0];
        } else {
            return $result;
        }
    }

    public function findAllExtIdsUsers(?string $extId): ?array
    {
        $result = $this->createQueryBuilder('u')
            ->where('u.extId = :val1')
            ->setParameter('val1', $extId)
            ->getQuery()
            ->getResult();

        if (is_array($result)) {
            return $result;
        } else {
            return [$result];
        }
    }

    public function findParent(?string $extId): ?User
    {
        return $this->createQueryBuilder('u')
            ->where('u.extId = :val1')
            ->setParameter('val1', $extId)
            ->andWhere('u.parent IS NULL')
            ->getQuery()
            ->getOneOrNullResult();
    }


    public function findOneById($id): ?User
    {
        return $this->createQueryBuilder('u')
            ->where('u.id = :val1')
            ->setParameter('val1', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function isUserHaveMigvan($extId): ?bool
    {
        $user = $this->createQueryBuilder('u')
            ->where('u.extId = :val1')
            ->setParameter('val1', $extId)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$user) {
            return false;
        }
        assert($user instanceof  User);
        $migvans = $user->getMigvans();

        return $migvans->count() > 0;
    }

    public function GetAgentClients(
        string $agentId,
        int $page = 1,
        int $usersPerPage = 50,
        ?string $search = null,
        ?bool $isSuperAgent = false,
        ?string $active = null
    ): Paginator {
        $queryBuilder = $this->createQueryBuilder('u');

        // Filter by agent ID if not super agent
        if (!$isSuperAgent) {
            $queryBuilder
                ->where('u.agent = :agentId')
                ->setParameter('agentId', $agentId);
        }

        // Filter out blocked users
        $queryBuilder->andWhere('u.isBlocked = false');
        $queryBuilder->andWhere('u.role = :roleUser');
        $queryBuilder->setParameter('roleUser', 'ROLE_USER');

        // Search filter (if search is provided)
        if ($search !== null) {
            $queryBuilder
                ->andWhere($queryBuilder->expr()->orX(
                    $queryBuilder->expr()->like('u.extId', ':search'),
                    $queryBuilder->expr()->like('u.name', ':search')
                ))
                ->setParameter('search', '%' . $search . '%');
        }

        // Active filter (if active is provided)
        if ($active === 'active') {
            $queryBuilder->andWhere('u.isRegistered = true');
        } elseif ($active === 'inactive') {
            $queryBuilder->andWhere('u.isRegistered = false');
        }

        // Pagination offset calculation
        $offset = ($page - 1) * $usersPerPage;

        // Paginator setup
        $doctrinePaginator = new DoctrinePaginator($queryBuilder);
        $doctrinePaginator->getQuery()
            ->setFirstResult($offset)
            ->setMaxResults($usersPerPage);

        $paginator = new Paginator($doctrinePaginator);

        return $paginator;
    }
    public function GetUnregisteredUsers()
    {

        return $this->createQueryBuilder('u')
            ->where('u.isBlocked = false')
            ->andWhere('u.isRegistered = true')
            ->getQuery()
            ->getResult();
    }

    public function GetAllAgents()
    {

        return $this->createQueryBuilder('u')
            ->where('u.isAgent = true')
            ->getQuery()
            ->getResult();
    }

    public function GetAllOneSignalAppIds()
    {
        return $this->createQueryBuilder('u')
            ->where('u.oneSignalAppId IS NOT NULL')
            ->getQuery()
            ->getResult();
    }

    public function GetAllRegisteredUsers()
    {
        return $this->createQueryBuilder('u')
            ->where('u.isRegistered = true')
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return User[] Returns an array of User objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?User
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
