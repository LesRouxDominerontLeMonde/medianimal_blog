<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Controller\Admin\ArticleCrudController;
use App\Entity\Article;
use App\Entity\Creneau;
use App\Entity\RendezVous;
use App\Repository\RendezVousRepository;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private RendezVousRepository $rendezVousRepository
    ) {
    }

    public function index(): Response
    {
        // Afficher un dashboard avec les statistiques des rendez-vous
        $stats = $this->rendezVousRepository->countByStatut();
        $prochains = $this->rendezVousRepository->findRendezVousAVenir();

        return $this->render('admin/dashboard.html.twig', [
            'stats' => $stats,
            'prochains_rdv' => array_slice($prochains, 0, 5), // Les 5 prochains
        ]);

        // Option 1. You can make your dashboard redirect to some common page of your backend
        //
        // 1.1) If you have enabled the "pretty URLs" feature:
        // return $this->redirectToRoute('admin_user_index');
        //
        // 1.2) Same example but using the "ugly URLs" that were used in previous EasyAdmin versions:
        // $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        // return $this->redirect($adminUrlGenerator->setController(OneOfYourCrudController::class)->generateUrl());

        // Option 2. You can make your dashboard redirect to different pages depending on the user
        //
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirectToRoute('...');
        // }

        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        //
        // return $this->render('some/path/my-dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Medianimal Blog - Administration');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-home');
        
        yield MenuItem::section('Contenu');
        yield MenuItem::linkToCrud('Articles', 'fas fa-newspaper', Article::class);
        
        yield MenuItem::section('Rendez-vous');
        yield MenuItem::linkToCrud('Créneaux', 'fas fa-calendar-plus', Creneau::class)
            ->setPermission('ROLE_ADMIN');
        yield MenuItem::linkToCrud('Rendez-vous', 'fas fa-calendar-check', RendezVous::class)
            ->setPermission('ROLE_ADMIN');
            
        yield MenuItem::section('Administration');
        yield MenuItem::linkToRoute('Mon Compte', 'fas fa-user-cog', 'admin_account')
            ->setPermission('ROLE_ADMIN');
    }
}
