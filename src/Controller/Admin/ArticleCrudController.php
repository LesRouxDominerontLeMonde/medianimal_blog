<?php

namespace App\Controller\Admin;

use App\Entity\Article;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use Vich\UploaderBundle\Form\Type\VichImageType;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use Symfony\Component\Validator\Constraints\Date;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;


class ArticleCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Article::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            NumberField::new('id')
                ->hideOnForm(),
            TextField::new('title'),
            TextareaField::new('content')
                ->setFormTypeOption('attr', ['class' => 'trumbowyg-editor']),
            
            // Section Publication
            FormField::addPanel('Publication')->setIcon('fa fa-eye'),
            
            BooleanField::new('isPublished', 'Article publié')
                ->setHelp('Contrôle si l\'article est visible publiquement')
                ->renderAsSwitch(false),
            
            DateTimeField::new('publishedAt', 'Date de publication')
                ->setHelp('Date effective d\'affichage public (peut être dans le futur)')
                ->setRequired(false),
            
            // Section Images
            FormField::addPanel('Images')->setIcon('fa fa-image'),
            
            // Image principale
            FormField::addFieldset('Image principale')
                ->setHelp('Formats acceptés : JPEG, PNG, WebP | Taille max : 2 Mo | Résolution min : 50 000 pixels'),
            
            ImageField::new('imageMainPath', 'Image actuelle')
                ->setBasePath('/images/articles')
                ->setUploadDir('public/images/articles')
                ->onlyOnIndex()
                ->setHelp('Image principale actuellement utilisée'),
                
            ImageField::new('imageMainPath', 'Image principale actuelle')
                ->setBasePath('/images/articles')
                ->setUploadDir('public/images/articles')
                ->hideOnIndex()
                ->onlyOnForms()
                ->setFormTypeOption('required', false)
                ->setHelp('Image actuellement utilisée (laissez vide pour conserver)'),
                
            TextField::new('imageMainFile', 'Nouvelle image principale')
                ->setFormType(VichImageType::class)
                ->hideOnIndex()
                ->onlyOnForms()
                ->setHelp('Téléchargez une nouvelle image pour remplacer l\'actuelle (optionnel en édition)'),
            
            // Image alternative
            FormField::addFieldset('Image alternative (optionnelle)')
                ->setHelp('Image secondaire pour offrir plus de choix à Google'),
            
            ImageField::new('imageAltPath', 'Image alternative')
                ->setBasePath('/images/articles')
                ->setUploadDir('public/images/articles')
                ->onlyOnIndex(),
                
            ImageField::new('imageAltPath', 'Image alternative actuelle')
                ->setBasePath('/images/articles')
                ->setUploadDir('public/images/articles')
                ->hideOnIndex()
                ->onlyOnForms()
                ->setFormTypeOption('required', false)
                ->setHelp('Image alternative actuellement utilisée'),
                
            TextField::new('imageAltFile', 'Nouvelle image alternative')
                ->setFormType(VichImageType::class)
                ->hideOnIndex()
                ->onlyOnForms()
                ->setHelp('Téléchargez une nouvelle image alternative (optionnel)'),
            
            // Section Métadonnées
            FormField::addPanel('Métadonnées')->setIcon('fa fa-info'),
            DateTimeField::new('createdAt', 'Date de création')
                ->hideOnForm()
        ];
    }
}
