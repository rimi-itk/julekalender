<?php

namespace App\Repository;

use App\Entity\Julekalender;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Julekalender|null find($id, $lockMode = null, $lockVersion = null)
 * @method Julekalender|null findOneBy(array $criteria, array $orderBy = null)
 * @method Julekalender[]    findAll()
 * @method Julekalender[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JulekalenderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Julekalender::class);
    }

    // /**
    //  * @return Julekalender[] Returns an array of Julekalender objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('j.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Julekalender
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
