<?php

namespace App\Repository;

use App\Entity\Medecin;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Medecin>
 */
class MedecinRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Medecin::class);
    }

    /**
     * Search medecins by name, specialization, email or license number
     * @param string $search
     * @return Medecin[]
     */
    public function search(string $search): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.first_name LIKE :search')
            ->orWhere('m.last_name LIKE :search')
            ->orWhere('m.specialization LIKE :search')
            ->orWhere('m.email LIKE :search')
            ->orWhere('m.license_number LIKE :search')
            ->setParameter('search', '%' . $search . '%')
            ->orderBy('m.last_name', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

//    /**
//     * @return Medecin[] Returns an array of Medecin objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Medecin
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
