<?php

namespace App\Controller;

use App\Repository\ProfessionnelRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Map\Map;
use Symfony\UX\Map\Point;
use Symfony\UX\Map\Marker;
use Symfony\UX\Map\InfoWindow;

final class MapController extends AbstractController
{
    #[Route('/map', name: 'app_map', methods: ['GET'])]
    public function index(ProfessionnelRepository $professionnelRepository): Response
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
                
                // Création du contenu de la popup
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

        return $this->render('map/index.html.twig', [
            'map' => $map,
            'professionnels' => $professionnels,
            'totalProfessionnels' => count($professionnels)
        ]);
    }
} 