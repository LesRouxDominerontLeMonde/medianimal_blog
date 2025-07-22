<?php

namespace App\Service;

use App\Repository\CreneauRepository;
use Psr\Log\LoggerInterface;

class NettoyageCreneauxService
{
    public function __construct(
        private CreneauRepository $creneauRepository,
        private LoggerInterface $logger
    ) {
    }

    /**
     * Supprime automatiquement les créneaux passés non réservés
     */
    public function supprimerCreneauxPassesNonReserves(): array
    {
        $results = [
            'nombre_supprimes' => 0,
            'erreurs' => [],
            'success' => false
        ];

        try {
            // Récupérer d'abord les créneaux qui vont être supprimés pour le log
            $creneauxASupprimer = $this->creneauRepository->findCreneauxPassesNonReserves();
            $nombreCreneaux = count($creneauxASupprimer);

            if ($nombreCreneaux === 0) {
                $results['success'] = true;
                $this->logger->info('Nettoyage des créneaux : aucun créneau passé non réservé à supprimer.');
                return $results;
            }

            // Logger les créneaux qui vont être supprimés
            $this->logger->info(sprintf(
                'Nettoyage des créneaux : %d créneaux passés non réservés vont être supprimés.',
                $nombreCreneaux
            ));

            foreach ($creneauxASupprimer as $creneau) {
                $this->logger->debug(sprintf(
                    'Suppression du créneau ID %d : %s le %s',
                    $creneau->getId(),
                    $creneau->getTitre() ?: 'Sans titre',
                    $creneau->getDateDebut()->format('d/m/Y H:i')
                ));
            }

            // Effectuer la suppression
            $nombreSupprimes = $this->creneauRepository->supprimerCreneauxPassesNonReserves();

            $results['nombre_supprimes'] = $nombreSupprimes;
            $results['success'] = true;

            $this->logger->info(sprintf(
                'Nettoyage des créneaux terminé avec succès : %d créneaux supprimés.',
                $nombreSupprimes
            ));

        } catch (\Exception $e) {
            $results['erreurs'][] = $e->getMessage();
            $this->logger->error('Erreur lors du nettoyage des créneaux : ' . $e->getMessage());
        }

        return $results;
    }

    /**
     * Vérifie s'il y a des créneaux passés non réservés
     */
    public function aDesCreneauxANettoyer(): bool
    {
        try {
            $creneaux = $this->creneauRepository->findCreneauxPassesNonReserves();
            return count($creneaux) > 0;
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la vérification des créneaux à nettoyer : ' . $e->getMessage());
            return false;
        }
    }
} 