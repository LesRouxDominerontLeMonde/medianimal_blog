<?php

namespace App\Controller\Admin;

use App\Entity\Commentaire;
use App\Entity\Professionnel;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;

class CommentaireCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Commentaire::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Commentaire')
            ->setEntityLabelInPlural('Commentaires')
            ->setPageTitle('index', 'Gestion des commentaires')
            ->setDefaultSort(['dateCreation' => 'DESC'])
            ->setPaginatorPageSize(20)
            ->setSearchFields(['nom', 'email', 'commentaire', 'professionnel.nom', 'professionnel.prenom', 'professionnel.ville'])
            ->setEntityPermission('ROLE_ADMIN');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(DateTimeFilter::new('dateCreation', 'Date de création'))
            ->add('rating', 'Note')
            ->add('isVisible', 'Visible')
            ->add('nom', 'Nom du commentateur')
            ->add('email', 'Email');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->hideOnForm(),

            AssociationField::new('professionnel', 'Professionnel')
                ->setColumns('col-md-6')
                ->onlyOnIndex(),

            AssociationField::new('professionnel', 'Professionnel')
                ->setRequired(true)
                ->hideOnIndex(),

            TextField::new('nom', 'Nom')
                ->setColumns('col-md-6'),

            EmailField::new('email', 'Email')
                ->setColumns('col-md-6'),

            TelephoneField::new('telephone', 'Téléphone')
                ->setColumns('col-md-6'),

            IntegerField::new('rating', 'Note')
                ->setColumns('col-md-6')
                ->formatValue(function ($value, $entity) {
                    if ($value) {
                        $stars = str_repeat('⭐', $value) . str_repeat('☆', 5 - $value);
                        return $value . '/5 ' . $stars;
                    }
                    return '';
                })
                ->setHelp('Note sur 5 étoiles'),

            TextareaField::new('commentaire', 'Commentaire')
                ->setColumns('col-md-12')
                ->formatValue(function ($value, $entity) {
                    if (strlen($value) > 100) {
                        return substr($value, 0, 100) . '...';
                    }
                    return $value;
                })
                ->onlyOnIndex(),

            TextareaField::new('commentaire', 'Commentaire')
                ->setColumns('col-md-12')
                ->setFormTypeOptions([
                    'attr' => [
                        'rows' => 4,
                        'placeholder' => 'Contenu du commentaire'
                    ]
                ])
                ->hideOnIndex(),

            DateTimeField::new('dateCreation', 'Date de création')
                ->setColumns('col-md-6')
                ->setFormat('dd/MM/yyyy HH:mm')
                ->hideOnForm(),

            BooleanField::new('isVisible', 'Visible')
                ->setColumns('col-md-6')
                ->setHelp('Contrôle si le commentaire est affiché publiquement')
                ->renderAsSwitch(false),
        ];
    }
} 