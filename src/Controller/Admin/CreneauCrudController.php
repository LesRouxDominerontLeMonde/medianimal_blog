<?php

namespace App\Controller\Admin;

use App\Entity\Creneau;
use App\Form\GenerationCreneauxType;
use App\Service\GenerateurCreneaux;
use App\Service\NettoyageCreneauxService;
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
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CreneauCrudController extends AbstractCrudController
{
    public function __construct(
        private GenerateurCreneaux $generateurCreneaux,
        private AdminUrlGenerator $adminUrlGenerator,
        private NettoyageCreneauxService $nettoyageCreneauxService
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
                return $action->setLabel('Créer un créneau');
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

    /**
     * Méthode appelée lors de la création d'un nouveau créneau
     */
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        parent::persistEntity($entityManager, $entityInstance);
        
        /** @var Creneau $entityInstance */
        $this->addFlash('success', sprintf(
            'Le créneau "%s" du %s a été créé avec succès !',
            $entityInstance->getTitre() ?: 'Sans titre',
            $entityInstance->getDateDebut()->format('d/m/Y à H:i')
        ));
    }

    /**
     * Méthode appelée lors de la modification d'un créneau
     */
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        parent::updateEntity($entityManager, $entityInstance);
        
        /** @var Creneau $entityInstance */
        $this->addFlash('success', sprintf(
            'Le créneau "%s" du %s a été modifié avec succès !',
            $entityInstance->getTitre() ?: 'Sans titre',
            $entityInstance->getDateDebut()->format('d/m/Y à H:i')
        ));
    }

    /**
     * Méthode appelée lors de la suppression d'un créneau
     */
    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        /** @var Creneau $entityInstance */
        $titre = $entityInstance->getTitre() ?: 'Sans titre';
        $dateFormatee = $entityInstance->getDateDebut()->format('d/m/Y à H:i');
        
        parent::deleteEntity($entityManager, $entityInstance);
        
        $this->addFlash('success', sprintf(
            'Le créneau "%s" du %s a été supprimé avec succès !',
            $titre,
            $dateFormatee
        ));
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

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
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
                            $this->addFlash('danger', $erreur);
                        }
                    }
                }
            } else {
                // Formulaire soumis mais invalide - ajouter les erreurs comme flash messages
                $this->addFlash('danger', 'Le formulaire contient des erreurs. Veuillez vérifier les champs.');
                
                // Log simple des erreurs
                $errorCount = count($form->getErrors());
                if ($errorCount > 0) {
                    $this->addFlash('danger', "Nombre d'erreurs détectées : $errorCount");
                }
            }
        }

        return $this->render('admin/generer_creneaux.html.twig', [
            'form' => $form,
            'preview' => $preview,
            'results' => $results,
        ]);
    }



    /**
     * Surcharge pour déclencher automatiquement le nettoyage lors de l'affichage de la liste
     */
    public function configureResponseParameters(KeyValueStore $responseParameters): KeyValueStore
    {
        // Déclencher automatiquement le nettoyage des créneaux passés (silencieusement)
        $this->nettoyageAutomatiqueSilencieux();

        return parent::configureResponseParameters($responseParameters);
    }

    /**
     * Nettoyage automatique silencieux (sans message flash)
     */
    private function nettoyageAutomatiqueSilencieux(): void
    {
        try {
            // Vérifier d'abord s'il y a des créneaux à nettoyer pour éviter les logs inutiles
            if ($this->nettoyageCreneauxService->aDesCreneauxANettoyer()) {
                $results = $this->nettoyageCreneauxService->supprimerCreneauxPassesNonReserves();
                
                // Optionnel : ajouter un message flash discret seulement si des créneaux ont été supprimés
                if ($results['success'] && $results['nombre_supprimes'] > 0) {
                    $this->addFlash('info', sprintf(
                        'Nettoyage automatique : %d créneaux passés ont été supprimés.',
                        $results['nombre_supprimes']
                    ));
                }
            }
        } catch (\Exception $e) {
            // Log l'erreur mais ne pas interrompre l'affichage de la page
            error_log('Erreur lors du nettoyage automatique des créneaux : ' . $e->getMessage());
        }
    }
} 