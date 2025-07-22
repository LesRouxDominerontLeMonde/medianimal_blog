<?php

namespace App\Repository;

use App\Entity\Creneau;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Creneau>
 */
class CreneauRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Creneau::class);
    }

    /**
     * Récupère les créneaux disponibles à partir d'une date donnée
     */
    public function findCreneauxDisponibles(\DateTimeInterface $dateMin = null): array
    {
        $qb = $this->createQueryBuilder('c')
            ->where('c.disponible = :disponible')
            ->andWhere('c.dateDebut >= :dateMin')
            ->setParameter('disponible', true)
            ->setParameter('dateMin', $dateMin ?: new \DateTime())
            ->orderBy('c.dateDebut', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Récupère les créneaux d'une période donnée
     */
    public function findCreneauxByPeriode(\DateTimeInterface $dateDebut, \DateTimeInterface $dateFin): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.dateDebut >= :dateDebut')
            ->andWhere('c.dateDebut <= :dateFin')
            ->setParameter('dateDebut', $dateDebut)
            ->setParameter('dateFin', $dateFin)
            ->orderBy('c.dateDebut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les créneaux disponibles d'une période donnée
     */
    public function findCreneauxDisponiblesByPeriode(\DateTimeInterface $dateDebut, \DateTimeInterface $dateFin): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.dateDebut >= :dateDebut')
            ->andWhere('c.dateDebut <= :dateFin')
            ->andWhere('c.disponible = :disponible')
            ->setParameter('dateDebut', $dateDebut)
            ->setParameter('dateFin', $dateFin)
            ->setParameter('disponible', true)
            ->orderBy('c.dateDebut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Vérifie si un créneau est libre (pas de chevauchement)
     */
    public function isCreneauLibre(\DateTimeInterface $dateDebut, \DateTimeInterface $dateFin, ?int $excludeId = null): bool
    {
        $qb = $this->createQueryBuilder('c')
            ->where('c.dateDebut < :dateFin')
            ->andWhere('c.dateFin > :dateDebut')
            ->setParameter('dateDebut', $dateDebut)
            ->setParameter('dateFin', $dateFin);

        if ($excludeId) {
            $qb->andWhere('c.id != :excludeId')
                ->setParameter('excludeId', $excludeId);
        }

        return $qb->getQuery()->getResult() === [];
    }

    /**
     * Récupère les créneaux passés non réservés
     */
    public function findCreneauxPassesNonReserves(): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.rendezVous', 'rv')
            ->where('c.dateFin < :now')
            ->andWhere('rv.id IS NULL')
            ->setParameter('now', new \DateTime())
            ->orderBy('c.dateDebut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Supprime les créneaux passés non réservés
     */
    public function supprimerCreneauxPassesNonReserves(): int
    {
        // Récupérer d'abord les IDs des créneaux à supprimer
        $creneauxIds = $this->createQueryBuilder('c')
            ->select('c.id')
            ->leftJoin('c.rendezVous', 'rv')
            ->where('c.dateFin < :now')
            ->andWhere('rv.id IS NULL')
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getResult();

        if (empty($creneauxIds)) {
            return 0;
        }

        // Extraire les IDs
        $ids = array_column($creneauxIds, 'id');

        // Supprimer les créneaux par IDs
        return $this->createQueryBuilder('c')
            ->delete()
            ->where('c.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->execute();
    }
} 