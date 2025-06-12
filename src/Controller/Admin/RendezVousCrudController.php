<?php

namespace App\Controller\Admin;

use App\Entity\RendezVous;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;

class RendezVousCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return RendezVous::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Rendez-vous')
            ->setEntityLabelInPlural('Rendez-vous')
            ->setSearchFields(['nom', 'prenom', 'email', 'telephone'])
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPaginatorPageSize(20);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->remove(Crud::PAGE_INDEX, Action::NEW) // Empêcher la création manuelle
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action->setLabel('Modifier');
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action->setLabel('Supprimer');
            })
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action->setLabel('Voir détails');
            });
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(ChoiceFilter::new('statut')->setChoices([
                'En attente' => 'en_attente',
                'Confirmé' => 'confirme',
                'Annulé' => 'annule',
                'Terminé' => 'termine',
            ]))
            ->add(DateTimeFilter::new('createdAt'));
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            
            TextField::new('nomComplet', 'Client')
                ->onlyOnIndex(),
            
            TextField::new('prenom', 'Prénom')
                ->hideOnIndex()
                ->setColumns(6),
            
            TextField::new('nom', 'Nom')
                ->hideOnIndex()
                ->setColumns(6),
            
            EmailField::new('email', 'Email')
                ->setColumns(6),
            
            TelephoneField::new('telephone', 'Téléphone')
                ->setColumns(6),
            
            AssociationField::new('creneau', 'Créneau')
                ->setFormTypeOption('disabled', true) // Empêcher la modification du créneau
                ->formatValue(function ($value, $entity) {
                    if ($entity->getCreneau()) {
                        return $entity->getCreneau()->getDateDebut()->format('d/m/Y H:i') . ' - ' . 
                               $entity->getCreneau()->getDateFin()->format('H:i');
                    }
                    return '';
                }),
            
            ChoiceField::new('statut', 'Statut')
                ->setChoices([
                    'En attente' => 'en_attente',
                    'Confirmé' => 'confirme',
                    'Annulé' => 'annule',
                    'Terminé' => 'termine',
                ])
                ->renderAsBadges([
                    'en_attente' => 'warning',
                    'confirme' => 'success',
                    'annule' => 'danger',
                    'termine' => 'secondary',
                ])
                ->setColumns(6),
            
            TextareaField::new('message', 'Message du client')
                ->hideOnIndex()
                ->setNumOfRows(3)
                ->setFormTypeOption('disabled', true), // En lecture seule
            
            DateTimeField::new('createdAt', 'Demandé le')
                ->setFormat('dd/MM/yyyy HH:mm')
                ->setFormTypeOption('disabled', true), // En lecture seule
        ];
    }
} 