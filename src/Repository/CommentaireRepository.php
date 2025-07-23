<?php

namespace App\Repository;

use App\Entity\Commentaire;
use App\Entity\Professionnel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Commentaire>
 */
class CommentaireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commentaire::class);
    }

    /**
     * Trouve les commentaires visibles pour un professionnel
     */
    public function findVisibleByProfessionnel(Professionnel $professionnel): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.professionnel = :professionnel')
            ->andWhere('c.isVisible = true')
            ->setParameter('professionnel', $professionnel)
            ->orderBy('c.dateCreation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Calcule la note moyenne pour un professionnel
     */
    public function getAverageRatingForProfessionnel(Professionnel $professionnel): float
    {
        $result = $this->createQueryBuilder('c')
            ->select('AVG(c.rating) as average')
            ->where('c.professionnel = :professionnel')
            ->andWhere('c.isVisible = true')
            ->setParameter('professionnel', $professionnel)
            ->getQuery()
            ->getSingleScalarResult();

        return $result ? round((float)$result, 1) : 0.0;
    }

    /**
     * Compte le nombre de commentaires pour un professionnel
     */
    public function countCommentairesForProfessionnel(Professionnel $professionnel): int
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.professionnel = :professionnel')
            ->andWhere('c.isVisible = true')
            ->setParameter('professionnel', $professionnel)
            ->getQuery()
            ->getSingleScalarResult();
    }
} 