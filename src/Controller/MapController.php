<?php

namespace App\Controller;

use App\Entity\Commentaire;
use App\Entity\Professionnel;
use App\Form\CommentaireType;
use App\Form\ProfessionnelType;
use App\Repository\CommentaireRepository;
use App\Repository\ProfessionnelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Map\Map;
use Symfony\UX\Map\Point;
use Symfony\UX\Map\Marker;
use Symfony\UX\Map\InfoWindow;

final class MapController extends AbstractController
{
    #[Route('/map', name: 'app_map', methods: ['GET'])]
    public function index(ProfessionnelRepository $professionnelRepository, CommentaireRepository $commentaireRepository): Response
    {
        // Récupération de tous les professionnels avec des coordonnées valides
        $professionnels = $professionnelRepository->createQueryBuilder('p')
            ->where('p.latitude IS NOT NULL')
            ->andWhere('p.longitude IS NOT NULL')
            ->getQuery()
            ->getResult();

        // Création de la carte centrée sur Lyon
        $map = (new Map())
            ->center(new Point(45.7640, 4.8357)) // Lyon
            ->zoom(7);

        // Ajout des marqueurs pour chaque professionnel
        foreach ($professionnels as $professionnel) {
            if ($professionnel->hasCoordinates()) {
                $point = new Point($professionnel->getLatitude(), $professionnel->getLongitude());
                
                // Récupération des statistiques de commentaires
                $averageRating = $commentaireRepository->getAverageRatingForProfessionnel($professionnel);
                $totalCommentaires = $commentaireRepository->countCommentairesForProfessionnel($professionnel);
                
                // Génération des étoiles pour l'affichage
                $starsHtml = '';
                for ($i = 1; $i <= 5; $i++) {
                    if ($i <= $averageRating) {
                        $starsHtml .= '<span style="color: #fbbf24;">★</span>';
                    } elseif ($i - 0.5 <= $averageRating) {
                        $starsHtml .= '<span style="color: #fbbf24;">★</span>';
                    } else {
                        $starsHtml .= '<span style="color: #d1d5db;">★</span>';
                    }
                }
                
                // Rating HTML pour la popup
                $ratingHtml = '';
                if ($totalCommentaires > 0) {
                    $ratingHtml = sprintf(
                        '<div class="popup-rating" style="position: absolute; top: 10px; right: 10px; text-align: right; font-size: 0.8rem;">
                            <div style="color: #fbbf24; font-size: 0.9rem;">%s</div>
                            <div style="color: #666; margin-top: 2px;">%.1f (%d avis)</div>
                        </div>',
                        $starsHtml,
                        $averageRating,
                        $totalCommentaires
                    );
                }
                
                // Création du contenu de la popup
                $popupContent = sprintf(
                    '<div class="map-popup" style="position: relative; min-width: 280px; padding-top: %s;">
                        %s
                        <h5 class="popup-title">%s %s</h5>
                        <div class="popup-info">
                            <p><strong>📍 Adresse :</strong><br>%s</p>
                            <p><strong>📞 Téléphone :</strong><br><a href="tel:%s">%s</a></p>
                            %s
                            <div class="popup-actions" style="margin-top: 10px;">
                                <a href="/%s" class="btn btn-primary btn-sm">Voir plus d\'infos</a>
                            </div>
                        </div>
                    </div>',
                    $totalCommentaires > 0 ? '25px' : '10px',
                    $ratingHtml,
                    htmlspecialchars($professionnel->getPrenom()),
                    htmlspecialchars($professionnel->getNom()),
                    htmlspecialchars($professionnel->getAdresseComplete()),
                    htmlspecialchars($professionnel->getTelephone()),
                    htmlspecialchars($professionnel->getTelephone()),
                    $professionnel->getSiteWeb() ? 
                        sprintf('<p><strong>🌐 Site web :</strong><br><a href="%s" target="_blank">%s</a></p>', 
                            htmlspecialchars($professionnel->getSiteWeb()), 
                            htmlspecialchars($professionnel->getSiteWeb())
                        ) : '',
                    htmlspecialchars($professionnel->getSlug())
                );

                // Création de l'InfoWindow (popup)
                $infoWindow = new InfoWindow(
                    headerContent: sprintf('<strong>%s %s</strong>', 
                        htmlspecialchars($professionnel->getPrenom()), 
                        htmlspecialchars($professionnel->getNom())
                    ),
                    content: $popupContent
                );
                
                // Création du marqueur avec popup
                $marker = new Marker(
                    position: $point,
                    title: $professionnel->__toString(),
                    infoWindow: $infoWindow
                );
                
                $map->addMarker($marker);
            }
        }

        // Création du formulaire pour ajouter un professionnel
        $nouveauProfessionnel = new Professionnel();
        $professionnelForm = $this->createForm(ProfessionnelType::class, $nouveauProfessionnel);

        return $this->render('map/index.html.twig', [
            'map' => $map,
            'professionnels' => $professionnels,
            'totalProfessionnels' => count($professionnels),
            'professionnelForm' => $professionnelForm->createView()
        ]);
    }

    #[Route('/map/ajouter-professionnel', name: 'app_map_add_professionnel', methods: ['POST'])]
    public function ajouterProfessionnel(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $professionnel = new Professionnel();
        $form = $this->createForm(ProfessionnelType::class, $professionnel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($professionnel);
            $entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'message' => 'Merci d\'avoir aidé à remplir cette carte :D',
                'professionnel' => [
                    'slug' => $professionnel->getSlug(),
                    'nom' => $professionnel->getPrenom() . ' ' . $professionnel->getNom()
                ]
            ]);
        }

        // Récupération des erreurs
        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $errors[] = $error->getMessage();
        }

        return new JsonResponse([
            'success' => false,
            'errors' => $errors
        ], 400);
    }

    #[Route('/{slug}', name: 'app_professionnel_show', methods: ['GET', 'POST'])]
    public function show(
        string $slug, 
        Request $request,
        ProfessionnelRepository $professionnelRepository,
        CommentaireRepository $commentaireRepository,
        EntityManagerInterface $entityManager
    ): Response {
        // Recherche du professionnel par slug
        $professionnels = $professionnelRepository->findAll();
        $professionnel = null;
        
        foreach ($professionnels as $pro) {
            if ($pro->getSlug() === $slug) {
                $professionnel = $pro;
                break;
            }
        }

        if (!$professionnel) {
            throw $this->createNotFoundException('Professionnel non trouvé');
        }

        // Récupération des commentaires
        $commentaires = $commentaireRepository->findVisibleByProfessionnel($professionnel);
        $averageRating = $commentaireRepository->getAverageRatingForProfessionnel($professionnel);
        $totalCommentaires = $commentaireRepository->countCommentairesForProfessionnel($professionnel);

        // Création du formulaire de commentaire
        $commentaire = new Commentaire();
        $commentaire->setProfessionnel($professionnel);
        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($commentaire);
            $entityManager->flush();

            $this->addFlash('success', 'Votre commentaire a été publié avec succès !');
            
            // Redirection pour éviter la resoumission
            return $this->redirectToRoute('app_professionnel_show', ['slug' => $slug]);
        }

        // Création de la carte centrée sur le professionnel
        $map = (new Map())
            ->center(new Point($professionnel->getLatitude(), $professionnel->getLongitude()))
            ->zoom(9);

        // Ajout du marqueur pour le professionnel
        $point = new Point($professionnel->getLatitude(), $professionnel->getLongitude());
        
        $popupContent = sprintf(
            '<div class="map-popup">
                <h5 class="popup-title">%s %s</h5>
                <div class="popup-info">
                    <p><strong>📍 Adresse :</strong><br>%s</p>
                    <p><strong>📞 Téléphone :</strong><br><a href="tel:%s">%s</a></p>
                    %s
                </div>
            </div>',
            htmlspecialchars($professionnel->getPrenom()),
            htmlspecialchars($professionnel->getNom()),
            htmlspecialchars($professionnel->getAdresseComplete()),
            htmlspecialchars($professionnel->getTelephone()),
            htmlspecialchars($professionnel->getTelephone()),
            $professionnel->getSiteWeb() ? 
                sprintf('<p><strong>🌐 Site web :</strong><br><a href="%s" target="_blank">%s</a></p>', 
                    htmlspecialchars($professionnel->getSiteWeb()), 
                    htmlspecialchars($professionnel->getSiteWeb())
                ) : ''
        );

        $infoWindow = new InfoWindow(
            headerContent: sprintf('<strong>%s %s</strong>', 
                htmlspecialchars($professionnel->getPrenom()), 
                htmlspecialchars($professionnel->getNom())
            ),
            content: $popupContent
        );
        
        $marker = new Marker(
            position: $point,
            title: $professionnel->__toString(),
            infoWindow: $infoWindow
        );
        
        $map->addMarker($marker);

        return $this->render('professionnel/show.html.twig', [
            'professionnel' => $professionnel,
            'map' => $map,
            'commentaires' => $commentaires,
            'averageRating' => $averageRating,
            'totalCommentaires' => $totalCommentaires,
            'commentaireForm' => $form->createView()
        ]);
    }
} 