<?php

namespace App\Repository;

use App\Entity\RendezVous;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RendezVous>
 */
class RendezVousRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RendezVous::class);
    }

    /**
     * Récupère les rendez-vous à venir
     */
    public function findRendezVousAVenir(): array
    {
        return $this->createQueryBuilder('r')
            ->join('r.creneau', 'c')
            ->where('c.dateDebut >= :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('c.dateDebut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les rendez-vous d'une période donnée
     */
    public function findRendezVousByPeriode(\DateTimeInterface $dateDebut, \DateTimeInterface $dateFin): array
    {
        return $this->createQueryBuilder('r')
            ->join('r.creneau', 'c')
            ->where('c.dateDebut >= :dateDebut')
            ->andWhere('c.dateDebut <= :dateFin')
            ->setParameter('dateDebut', $dateDebut)
            ->setParameter('dateFin', $dateFin)
            ->orderBy('c.dateDebut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les rendez-vous par statut
     */
    public function findByStatut(string $statut): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.statut = :statut')
            ->setParameter('statut', $statut)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte les rendez-vous par statut
     */
    public function countByStatut(): array
    {
        $result = $this->createQueryBuilder('r')
            ->select('r.statut, COUNT(r.id) as count')
            ->groupBy('r.statut')
            ->getQuery()
            ->getResult();

        $counts = [
            'en_attente' => 0,
            'confirme' => 0,
            'annule' => 0,
            'termine' => 0,
        ];

        foreach ($result as $row) {
            $counts[$row['statut']] = (int) $row['count'];
        }

        return $counts;
    }
} 