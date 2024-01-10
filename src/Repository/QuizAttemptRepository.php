<?php

namespace App\Repository;

use App\Entity\QuizAttempt;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<QuizAttempt>
 *
 * @method QuizAttempt|null find($id, $lockMode = null, $lockVersion = null)
 * @method QuizAttempt|null findOneBy(array $criteria, array $orderBy = null)
 * @method QuizAttempt[]    findAll()
 * @method QuizAttempt[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QuizAttemptRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QuizAttempt::class);
    }

//    /**
//     * @return QuizAttempt[] Returns an array of QuizAttempt objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('q')
//            ->andWhere('q.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('q.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?QuizAttempt
//    {
//        return $this->createQueryBuilder('q')
//            ->andWhere('q.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
