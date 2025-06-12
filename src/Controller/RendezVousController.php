<?php

namespace App\Controller;

use App\Entity\Creneau;
use App\Entity\RendezVous;
use App\Form\RendezVousType;
use App\Repository\CreneauRepository;
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
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/', name: 'app_rendez_vous')]
    public function index(): Response
    {
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
                'start' => $creneau->getDateDebut()->format('c'),
                'end' => $creneau->getDateFin()->format('c'),
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
        return $this->json([
            'id' => $creneau->getId(),
            'titre' => $creneau->getTitre(),
            'description' => $creneau->getDescription(),
            'dateDebut' => $creneau->getDateDebut()->format('d/m/Y H:i'),
            'dateFin' => $creneau->getDateFin()->format('H:i'),
            'disponible' => $creneau->isDisponible() && !$creneau->isReserve(),
            'reserve' => $creneau->isReserve(),
        ]);
    }
} 