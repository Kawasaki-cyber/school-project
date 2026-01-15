<?php

namespace App\Repository;

use App\Entity\Patient;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Patient>
 */
class PatientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Patient::class);
    }

    /**
     * Search patients by name, email, phone or patient number
     * @param string $search
     * @return Patient[]
     */
    public function search(string $search): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.first_name LIKE :search')
            ->orWhere('p.last_name LIKE :search')
            ->orWhere('p.email LIKE :search')
            ->orWhere('p.phone LIKE :search')
            ->orWhere('p.patient_number LIKE :search')
            ->setParameter('search', '%' . $search . '%')
            ->orderBy('p.last_name', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Try to find a single Patient by a full name string.
     * Accepts values like "John Doe" and matches case-insensitively.
     * Returns null when none or ambiguous.
     */
    public function findOneByFullName(string $fullName): ?Patient
    {
        $normalized = trim(preg_replace('/\s+/', ' ', $fullName) ?? '');
        if ($normalized === '') {
            return null;
        }

        $parts = explode(' ', $normalized);

        // Single token: try exact match against first OR last name
        if (count($parts) === 1) {
            $token = mb_strtolower($parts[0]);
            $results = $this->createQueryBuilder('p')
                ->where('LOWER(p.first_name) = :t')
                ->orWhere('LOWER(p.last_name) = :t')
                ->setParameter('t', $token)
                ->setMaxResults(2)
                ->getQuery()
                ->getResult();

            return count($results) === 1 ? $results[0] : null;
        }

        $first = mb_strtolower(array_shift($parts));
        $last = mb_strtolower(implode(' ', $parts));

        $findExact = function (string $fn, string $ln): ?Patient {
            $results = $this->createQueryBuilder('p')
                ->where('LOWER(p.first_name) = :fn')
                ->andWhere('LOWER(p.last_name) = :ln')
                ->setParameter('fn', $fn)
                ->setParameter('ln', $ln)
                ->setMaxResults(2)
                ->getQuery()
                ->getResult();

            return count($results) === 1 ? $results[0] : null;
        };

        // Try "First Last" then (when exactly 2 tokens) "Last First"
        $patient = $findExact($first, $last);
        if ($patient) {
            return $patient;
        }

        if (count(explode(' ', $normalized)) === 2) {
            $tokens = explode(' ', $normalized);
            $patient = $findExact(mb_strtolower($tokens[1]), mb_strtolower($tokens[0]));
            if ($patient) {
                return $patient;
            }
        }

        return null;
    }

//    /**
//     * @return Patient[] Returns an array of Patient objects
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

//    public function findOneBySomeField($value): ?Patient
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
