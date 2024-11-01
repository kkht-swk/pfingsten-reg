<?php

namespace App\Repository;

use App\Entity\PlayerInfo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PlayerInfo>
 *
 * @method PlayerInfo|null find($id, $lockMode = null, $lockVersion = null)
 * @method PlayerInfo|null findOneBy(array $criteria, array $orderBy = null)
 * @method PlayerInfo[]    findAll()
 * @method PlayerInfo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlayerInfoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlayerInfo::class);
    }

    public function save(PlayerInfo $pi, bool $flush = false): void 
    {
        $this->getEntityManager()->persist($pi);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function delete(PlayerInfo $pi, bool $flush = false): void 
    {
        $this->getEntityManager()->remove($pi);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    //    /**
    //     * @return PlayerInfo[] Returns an array of PlayerInfo objects
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

    //    public function findOneBySomeField($value): ?PlayerInfo
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
