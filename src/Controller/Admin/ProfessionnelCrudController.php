<?php

namespace App\Controller\Admin;

use App\Entity\Professionnel;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;

class ProfessionnelCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Professionnel::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Professionnel')
            ->setEntityLabelInPlural('Professionnels')
            ->setSearchFields(['nom', 'prenom', 'adresse', 'ville', 'codePostal', 'telephone'])
            ->setDefaultSort(['nom' => 'ASC'])
            ->setPaginatorPageSize(20);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('nom'))
            ->add(TextFilter::new('prenom'))
            ->add(TextFilter::new('adresse'))
            ->add(TextFilter::new('ville'))
            ->add(TextFilter::new('codePostal'));
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->hideOnForm(),
                
            TextField::new('nom')
                ->setLabel('Nom')
                ->setRequired(true)
                ->setColumns(6),
                
            TextField::new('prenom')
                ->setLabel('Prénom')
                ->setRequired(true)
                ->setColumns(6),
                
            TextField::new('adresse')
                ->setLabel('Adresse')
                ->setRequired(true)
                ->setColumns(12),
                
            TextField::new('ville')
                ->setLabel('Ville')
                ->setRequired(true)
                ->setColumns(6),
                
            TextField::new('codePostal')
                ->setLabel('Code postal')
                ->setRequired(true)
                ->setColumns(6),
                
            NumberField::new('latitude')
                ->setLabel('Latitude')
                ->setRequired(true)
                ->setNumDecimals(6)
                ->setColumns(6)
                ->setHelp('Coordonnée GPS latitude (entre -90 et 90)'),
                
            NumberField::new('longitude')
                ->setLabel('Longitude')
                ->setRequired(true)
                ->setNumDecimals(6)
                ->setColumns(6)
                ->setHelp('Coordonnée GPS longitude (entre -180 et 180)'),
                
            TelephoneField::new('telephone')
                ->setLabel('Téléphone')
                ->setRequired(true)
                ->setColumns(6),
                
            UrlField::new('siteWeb')
                ->setLabel('Site web')
                ->setRequired(false)
                ->setColumns(6)
                ->hideOnIndex(),
        ];
    }
} 