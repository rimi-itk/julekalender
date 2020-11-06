<?php

namespace App\Repository;

use App\Entity\Laage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Laage|null find($id, $lockMode = null, $lockVersion = null)
 * @method Laage|null findOneBy(array $criteria, array $orderBy = null)
 * @method Laage[]    findAll()
 * @method Laage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LaageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Laage::class);
    }

    // /**
    //  * @return Laage[] Returns an array of Laage objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Laage
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
