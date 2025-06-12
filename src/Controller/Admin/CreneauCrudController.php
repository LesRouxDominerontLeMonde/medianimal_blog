<?php

namespace App\Controller\Admin;

use App\Entity\Creneau;
use App\Form\GenerationCreneauxType;
use App\Service\GenerateurCreneaux;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CreneauCrudController extends AbstractCrudController
{
    public function __construct(
        private GenerateurCreneaux $generateurCreneaux,
        private AdminUrlGenerator $adminUrlGenerator
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Creneau::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Créneau')
            ->setEntityLabelInPlural('Créneaux')
            ->setSearchFields(['titre', 'description'])
            ->setDefaultSort(['dateDebut' => 'ASC'])
            ->setPaginatorPageSize(20);
    }

    public function configureActions(Actions $actions): Actions
    {
        $genererCreneaux = Action::new('genererCreneaux', 'Générer des créneaux')
            ->setIcon('fas fa-magic')
            ->linkToRoute('admin_generer_creneaux')
            ->setCssClass('btn btn-success')
            ->createAsGlobalAction();

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $genererCreneaux)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel('Générer un créneau');
            })
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action->setLabel('Modifier');
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action->setLabel('Supprimer');
            })
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action->setLabel('Voir');
            });
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            
            TextField::new('titre', 'Titre')
                ->setHelp('Titre du créneau (ex: "Consultation", "Urgence")')
                ->setColumns(6),
            
            DateTimeField::new('dateDebut', 'Date et heure de début')
                ->setFormat('dd/MM/yyyy HH:mm')
                ->setColumns(6),
            
            DateTimeField::new('dateFin', 'Date et heure de fin')
                ->setFormat('dd/MM/yyyy HH:mm')
                ->setColumns(6),
            
            TextareaField::new('description', 'Description')
                ->setHelp('Description optionnelle du créneau')
                ->hideOnIndex()
                ->setNumOfRows(3),
            
            BooleanField::new('disponible', 'Disponible')
                ->setHelp('Décochez pour désactiver temporairement ce créneau')
                ->renderAsSwitch(false),
            
            DateTimeField::new('createdAt', 'Créé le')
                ->setFormat('dd/MM/yyyy HH:mm')
                ->onlyOnDetail(),
        ];
    }

    #[Route('/admin/creneaux/generer', name: 'admin_generer_creneaux')]
    public function genererCreneaux(Request $request): Response
    {
        // Créer les données par défaut
        $defaultData = [
            'titre_defaut' => 'Consultation',
            'description_defaut' => 'Consultation vétérinaire générale',
            'horaires' => [
                ['heure_debut' => \DateTime::createFromFormat('H:i', '09:00'), 'heure_fin' => \DateTime::createFromFormat('H:i', '09:30')],
                ['heure_debut' => \DateTime::createFromFormat('H:i', '10:00'), 'heure_fin' => \DateTime::createFromFormat('H:i', '10:30')],
                ['heure_debut' => \DateTime::createFromFormat('H:i', '11:00'), 'heure_fin' => \DateTime::createFromFormat('H:i', '11:30')],
                ['heure_debut' => \DateTime::createFromFormat('H:i', '14:00'), 'heure_fin' => \DateTime::createFromFormat('H:i', '14:30')],
                ['heure_debut' => \DateTime::createFromFormat('H:i', '15:00'), 'heure_fin' => \DateTime::createFromFormat('H:i', '15:30')],
                ['heure_debut' => \DateTime::createFromFormat('H:i', '16:00'), 'heure_fin' => \DateTime::createFromFormat('H:i', '16:30')],
            ],
            'jours_semaine' => [1, 2, 3, 4, 5], // Lundi à vendredi
            'date_debut' => new \DateTime('next monday'),
            'nombre_semaines' => 4,
            'ecraser_existants' => false,
        ];

        $form = $this->createForm(GenerationCreneauxType::class, $defaultData);
        $form->handleRequest($request);

        $preview = null;
        $results = null;

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            
            if ($request->request->has('preview')) {
                // Mode prévisualisation
                $preview = $this->generateurCreneaux->previsualiserCreneaux($data);
            } else {
                // Génération effective
                $results = $this->generateurCreneaux->genererCreneaux($data);
                
                if (empty($results['erreurs'])) {
                    $this->addFlash('success', sprintf(
                        '%d créneaux créés avec succès%s !',
                        $results['crees'],
                        $results['supprimes'] > 0 ? ' (' . $results['supprimes'] . ' supprimés)' : ''
                    ));
                    
                    // Rediriger vers la liste des créneaux
                    $url = $this->adminUrlGenerator
                        ->setController(self::class)
                        ->setAction(Action::INDEX)
                        ->generateUrl();
                    
                    return $this->redirect($url);
                } else {
                    foreach ($results['erreurs'] as $erreur) {
                        $this->addFlash('error', $erreur);
                    }
                }
            }
        }

        return $this->render('admin/generer_creneaux.html.twig', [
            'form' => $form,
            'preview' => $preview,
            'results' => $results,
        ]);
    }
} 