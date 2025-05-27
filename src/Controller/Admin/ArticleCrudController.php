<?php

namespace App\Controller\Admin;

use App\Entity\Article;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
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
            TextField::new('title'),
            TextareaField::new('content')
                ->setFormTypeOption('attr', ['class' => 'trumbowyg-editor']),
            DateTimeField::new('createdAt'),
            DateTimeField::new('publishedAt')
                /*
                ->setFormTypeOption('attr',['class' => 'trumbowyg-editor'])
                ->setFormTypeOption('widget', 'single_text')
                ->setFormTypeOption('html5', false)
                ->setFormTypeOption('format', 'dd/MM/yyyy HH:mm')
                ->setFormTypeOption('placeholder', 'dd/MM/yyyy HH:mm'),
                */
        ];
    }
}
