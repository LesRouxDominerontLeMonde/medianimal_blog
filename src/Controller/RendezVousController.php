<?php

namespace App\Controller;

use App\Entity\Creneau;
use App\Entity\RendezVous;
use App\Form\RendezVousType;
use App\Repository\CreneauRepository;
use App\Service\NettoyageCreneauxService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/rdv')]
class RendezVousController extends AbstractController
{
    public function __construct(
        private CreneauRepository $creneauRepository,
        private EntityManagerInterface $entityManager,
        private NettoyageCreneauxService $nettoyageCreneauxService
    ) {
    }

    #[Route('/', name: 'app_rendez_vous')]
    public function index(): Response
    {
        // Déclencher automatiquement le nettoyage des créneaux passés
        $this->nettoyageAutomatiqueSilencieux();
        
        return $this->render('rendez_vous/index.html.twig');
    }

    #[Route('/api/creneaux', name: 'app_rdv_api_creneaux', methods: ['GET'])]
    public function getCreneaux(Request $request): JsonResponse
    {
        $dateDebut = $request->query->get('start');
        $dateFin = $request->query->get('end');

        if (!$dateDebut || !$dateFin) {
            // Par défaut, récupérer les créneaux du mois courant
            $dateDebut = new \DateTime('first day of this month');
            $dateFin = new \DateTime('last day of this month');
        } else {
            $dateDebut = new \DateTime($dateDebut);
            $dateFin = new \DateTime($dateFin);
        }

        $creneaux = $this->creneauRepository->findCreneauxDisponiblesByPeriode($dateDebut, $dateFin);

        $events = [];
        foreach ($creneaux as $creneau) {
            $events[] = [
                'id' => $creneau->getId(),
                'title' => $creneau->getTitre() ?: 'Disponible',
                'start' => $creneau->getDateDebut()->format('Y-m-d H:i:s'),
                'end' => $creneau->getDateFin()->format('Y-m-d H:i:s'),
                'backgroundColor' => $creneau->isReserve() ? '#dc3545' : '#28a745',
                'borderColor' => $creneau->isReserve() ? '#dc3545' : '#28a745',
                'extendedProps' => [
                    'description' => $creneau->getDescription(),
                    'disponible' => $creneau->isDisponible() && !$creneau->isReserve(),
                ]
            ];
        }

        return $this->json($events);
    }

    #[Route('/reserver/{id}', name: 'app_rdv_reserver', methods: ['GET', 'POST'])]
    public function reserver(Creneau $creneau, Request $request): Response
    {
        // Vérifier que le créneau est disponible
        if (!$creneau->isDisponible() || $creneau->isReserve()) {
            $this->addFlash('error', 'Ce créneau n\'est plus disponible.');
            return $this->redirectToRoute('app_rendez_vous');
        }

        // Vérifier que le créneau est dans le futur
        if ($creneau->getDateDebut() <= new \DateTime()) {
            $this->addFlash('error', 'Impossible de réserver un créneau passé.');
            return $this->redirectToRoute('app_rendez_vous');
        }

        $rendezVous = new RendezVous();
        $rendezVous->setCreneau($creneau);

        $form = $this->createForm(RendezVousType::class, $rendezVous);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifier à nouveau la disponibilité avant d'enregistrer
            if (!$creneau->isDisponible() || $creneau->isReserve()) {
                $this->addFlash('error', 'Ce créneau a été réservé entre temps.');
                return $this->redirectToRoute('app_rendez_vous');
            }

            $this->entityManager->persist($rendezVous);
            $this->entityManager->flush();

            $this->addFlash('success', 'Votre rendez-vous a été réservé avec succès ! Nous vous contacterons pour confirmation.');
            
            return $this->redirectToRoute('app_rendez_vous_confirmation', ['id' => $rendezVous->getId()]);
        }

        return $this->render('rendez_vous/reserver.html.twig', [
            'creneau' => $creneau,
            'form' => $form,
        ]);
    }

    #[Route('/confirmation/{id}', name: 'app_rendez_vous_confirmation')]
    public function confirmation(RendezVous $rendezVous): Response
    {
        return $this->render('rendez_vous/confirmation.html.twig', [
            'rendezVous' => $rendezVous,
        ]);
    }

    #[Route('/api/creneau/{id}', name: 'app_rdv_api_creneau_details', methods: ['GET'])]
    public function getCreneauDetails(Creneau $creneau): JsonResponse
    {
        // Vérifier que le créneau est disponible
        if (!$creneau->isDisponible() || $creneau->isReserve()) {
            return $this->json([
                'disponible' => false,
                'message' => 'Ce créneau n\'est plus disponible.'
            ]);
        }

        // Vérifier que le créneau est dans le futur
        if ($creneau->getDateDebut() <= new \DateTime()) {
            return $this->json([
                'disponible' => false,
                'message' => 'Impossible de réserver un créneau passé.'
            ]);
        }

        // Créer le formulaire pour récupérer le HTML
        $rendezVous = new RendezVous();
        $rendezVous->setCreneau($creneau);
        $form = $this->createForm(RendezVousType::class, $rendezVous, [
            'action' => $this->generateUrl('app_rdv_reserver_ajax', ['id' => $creneau->getId()]),
            'method' => 'POST'
        ]);

        return $this->json([
            'id' => $creneau->getId(),
            'titre' => $creneau->getTitre(),
            'description' => $creneau->getDescription(),
            'dateDebut' => $creneau->getDateDebut()->format('d/m/Y H:i'),
            'dateFin' => $creneau->getDateFin()->format('H:i'),
            'disponible' => true,
            'formHtml' => $this->renderView('rendez_vous/_form_modal.html.twig', [
                'form' => $form->createView(),
                'creneau' => $creneau
            ])
        ]);
    }

    #[Route('/api/reserver/{id}', name: 'app_rdv_reserver_ajax', methods: ['POST'])]
    public function reserverAjax(Creneau $creneau, Request $request): JsonResponse
    {
        // Vérifier que le créneau est disponible
        if (!$creneau->isDisponible() || $creneau->isReserve()) {
            return $this->json([
                'success' => false,
                'message' => 'Ce créneau n\'est plus disponible.'
            ], 400);
        }

        // Vérifier que le créneau est dans le futur
        if ($creneau->getDateDebut() <= new \DateTime()) {
            return $this->json([
                'success' => false,
                'message' => 'Impossible de réserver un créneau passé.'
            ], 400);
        }

        $rendezVous = new RendezVous();
        $rendezVous->setCreneau($creneau);

        $form = $this->createForm(RendezVousType::class, $rendezVous);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifier à nouveau la disponibilité avant d'enregistrer
            if (!$creneau->isDisponible() || $creneau->isReserve()) {
                return $this->json([
                    'success' => false,
                    'message' => 'Ce créneau a été réservé entre temps.'
                ], 400);
            }

            $this->entityManager->persist($rendezVous);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Votre rendez-vous a été réservé avec succès ! Nous vous contacterons pour confirmation.',
                'redirect' => $this->generateUrl('app_rendez_vous')
            ]);
        }

        // Retourner les erreurs de validation
        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $errors[] = $error->getMessage();
        }

        return $this->json([
            'success' => false,
            'message' => 'Le formulaire contient des erreurs.',
            'errors' => $errors
        ], 400);
    }

    /**
     * Nettoyage automatique silencieux (sans message flash)
     */
    private function nettoyageAutomatiqueSilencieux(): void
    {
        try {
            // Vérifier d'abord s'il y a des créneaux à nettoyer pour éviter les logs inutiles
            if ($this->nettoyageCreneauxService->aDesCreneauxANettoyer()) {
                $this->nettoyageCreneauxService->supprimerCreneauxPassesNonReserves();
            }
        } catch (\Exception $e) {
            // Log l'erreur mais ne pas interrompre l'affichage de la page
            error_log('Erreur lors du nettoyage automatique des créneaux : ' . $e->getMessage());
        }
    }
} 