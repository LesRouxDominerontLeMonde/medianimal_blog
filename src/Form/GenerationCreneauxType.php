<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class GenerationCreneauxType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Configuration de la journée type
            ->add('titre_defaut', TextType::class, [
                'label' => 'Titre par défaut des créneaux',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'ex: Consultation, Urgence...'
                ]
            ])
            ->add('description_defaut', TextareaType::class, [
                'label' => 'Description par défaut',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Description qui apparaîtra sur tous les créneaux'
                ]
            ])
            
            // Horaires de la journée type
            ->add('horaires', CollectionType::class, [
                'label' => 'Horaires de la journée type',
                'entry_type' => HoraireCreneauType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'attr' => [
                    'class' => 'horaires-collection'
                ]
            ])
            
            // Jours de la semaine
            ->add('jours_semaine', ChoiceType::class, [
                'label' => 'Jours de la semaine',
                'choices' => [
                    'Lundi' => 1,
                    'Mardi' => 2,
                    'Mercredi' => 3,
                    'Jeudi' => 4,
                    'Vendredi' => 5,
                    'Samedi' => 6,
                    'Dimanche' => 7,
                ],
                'multiple' => true,
                'expanded' => true,
                'attr' => [
                    'class' => 'form-check-list'
                ]
            ])
            
            // Période de génération
            ->add('date_debut', DateType::class, [
                'label' => 'Date de début',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control',
                    'min' => (new \DateTime())->format('Y-m-d')
                ]
            ])
            ->add('nombre_semaines', IntegerType::class, [
                'label' => 'Nombre de semaines à générer',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 1,
                    'max' => 26
                ],
                'constraints' => [
                    new Assert\Range(['min' => 1, 'max' => 26])
                ]
            ])
            
            // Options avancées
            ->add('ecraser_existants', CheckboxType::class, [
                'label' => 'Écraser les créneaux existants aux mêmes horaires',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ],
                'help' => 'Si coché, les créneaux existants aux mêmes dates/heures seront supprimés'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}

class HoraireCreneauType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('heure_debut', TimeType::class, [
                'label' => 'Début',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control']
            ])
            ->add('heure_fin', TimeType::class, [
                'label' => 'Fin',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
} 