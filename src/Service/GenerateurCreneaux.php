<?php

namespace App\Service;

use App\Entity\Creneau;
use App\Repository\CreneauRepository;
use Doctrine\ORM\EntityManagerInterface;

class GenerateurCreneaux
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CreneauRepository $creneauRepository
    ) {
    }

    /**
     * Génère des créneaux basés sur une journée type
     */
    public function genererCreneaux(array $parametres): array
    {
        $results = [
            'crees' => 0,
            'supprimes' => 0,
            'erreurs' => []
        ];

        $dateDebut = $parametres['date_debut'];
        $nombreSemaines = $parametres['nombre_semaines'];
        $joursSemaine = $parametres['jours_semaine'];
        $horaires = $parametres['horaires'];
        $titreDefaut = $parametres['titre_defaut'];
        $descriptionDefaut = $parametres['description_defaut'];
        $ecraserExistants = $parametres['ecraser_existants'] ?? false;

        try {
            $this->entityManager->beginTransaction();

            // Calculer toutes les dates concernées
            $dates = $this->calculerDates($dateDebut, $nombreSemaines, $joursSemaine);

            foreach ($dates as $date) {
                foreach ($horaires as $horaire) {
                    // Créer les dates/heures de début et fin
                    $dateHeureDebut = clone $date;
                    
                    // Traiter les objets DateTime des horaires
                    $heureDebut = $horaire['heure_debut'];
                    if ($heureDebut instanceof \DateTimeInterface) {
                        $dateHeureDebut->setTime((int)$heureDebut->format('H'), (int)$heureDebut->format('i'));
                    } else {
                        // Fallback pour les chaînes de caractères
                        $heureDebutArray = explode(':', $heureDebut);
                        $dateHeureDebut->setTime((int)$heureDebutArray[0], (int)$heureDebutArray[1]);
                    }

                    $dateHeureFin = clone $date;
                    $heureFin = $horaire['heure_fin'];
                    if ($heureFin instanceof \DateTimeInterface) {
                        $dateHeureFin->setTime((int)$heureFin->format('H'), (int)$heureFin->format('i'));
                    } else {
                        // Fallback pour les chaînes de caractères
                        $heureFinArray = explode(':', $heureFin);
                        $dateHeureFin->setTime((int)$heureFinArray[0], (int)$heureFinArray[1]);
                    }

                    // Vérifier les chevauchements si on n'écrase pas
                    if (!$ecraserExistants) {
                        if (!$this->creneauRepository->isCreneauLibre($dateHeureDebut, $dateHeureFin)) {
                            $results['erreurs'][] = sprintf(
                                'Créneau en conflit le %s de %s à %s',
                                $date->format('d/m/Y'),
                                $heureDebut instanceof \DateTimeInterface ? $heureDebut->format('H:i') : $heureDebut,
                                $heureFin instanceof \DateTimeInterface ? $heureFin->format('H:i') : $heureFin
                            );
                            continue;
                        }
                    } else {
                        // Supprimer les créneaux existants
                        $this->supprimerCreneauxExistants($dateHeureDebut, $dateHeureFin);
                        $results['supprimes']++;
                    }

                    // Créer le nouveau créneau
                    $creneau = new Creneau();
                    $creneau->setDateDebut($dateHeureDebut);
                    $creneau->setDateFin($dateHeureFin);
                    $creneau->setTitre($titreDefaut);
                    $creneau->setDescription($descriptionDefaut);
                    $creneau->setDisponible(true);

                    $this->entityManager->persist($creneau);
                    $results['crees']++;
                }
            }

            $this->entityManager->flush();
            $this->entityManager->commit();

        } catch (\Exception $e) {
            $this->entityManager->rollback();
            $results['erreurs'][] = 'Erreur lors de la génération : ' . $e->getMessage();
        }

        return $results;
    }

    /**
     * Calcule toutes les dates concernées par la génération
     */
    private function calculerDates(\DateTimeInterface $dateDebut, int $nombreSemaines, array $joursSemaine): array
    {
        $dates = [];
        $date = new \DateTime($dateDebut->format('Y-m-d'));

        // Aller au début de la semaine (lundi)
        while ($date->format('N') != 1) {
            $date->modify('-1 day');
        }

        for ($semaine = 0; $semaine < $nombreSemaines; $semaine++) {
            for ($jour = 1; $jour <= 7; $jour++) {
                if (in_array($jour, $joursSemaine)) {
                    $dateJour = clone $date;
                    $dateJour->modify('+' . ($jour - 1) . ' days');
                    
                    // Ne pas créer de créneaux dans le passé
                    if ($dateJour >= new \DateTime('today')) {
                        $dates[] = $dateJour;
                    }
                }
            }
            $date->modify('+7 days');
        }

        return $dates;
    }

    /**
     * Supprime les créneaux existants aux mêmes horaires
     */
    private function supprimerCreneauxExistants(\DateTimeInterface $dateDebut, \DateTimeInterface $dateFin): void
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->delete(Creneau::class, 'c')
            ->where('c.dateDebut = :dateDebut')
            ->andWhere('c.dateFin = :dateFin')
            ->setParameter('dateDebut', $dateDebut)
            ->setParameter('dateFin', $dateFin);

        $qb->getQuery()->execute();
    }

    /**
     * Prévisualise les créneaux qui seraient créés
     */
    public function previsualiserCreneaux(array $parametres): array
    {
        $dateDebut = $parametres['date_debut'];
        $nombreSemaines = $parametres['nombre_semaines'];
        $joursSemaine = $parametres['jours_semaine'];
        $horaires = $parametres['horaires'];

        $dates = $this->calculerDates($dateDebut, $nombreSemaines, $joursSemaine);
        $preview = [];

        foreach ($dates as $date) {
            $joursNoms = [
                1 => 'Lundi', 2 => 'Mardi', 3 => 'Mercredi', 
                4 => 'Jeudi', 5 => 'Vendredi', 6 => 'Samedi', 7 => 'Dimanche'
            ];

            foreach ($horaires as $horaire) {
                // Traiter les objets DateTime des horaires
                $heureDebut = $horaire['heure_debut'];
                $heureFin = $horaire['heure_fin'];
                
                $heureDebutStr = $heureDebut instanceof \DateTimeInterface ? $heureDebut->format('H:i') : $heureDebut;
                $heureFinStr = $heureFin instanceof \DateTimeInterface ? $heureFin->format('H:i') : $heureFin;
                
                $preview[] = [
                    'date' => $date->format('d/m/Y'),
                    'jour' => $joursNoms[$date->format('N')],
                    'heure_debut' => $heureDebutStr,
                    'heure_fin' => $heureFinStr,
                ];
            }
        }

        return $preview;
    }
} 