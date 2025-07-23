<?php

namespace App\Form;

use App\Entity\Commentaire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommentaireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Votre nom',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez votre nom'
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Votre email',
                'help' => 'Votre email restera confidentiel et sera utilisé uniquement par l\'administrateur pour détecter des faux commentaires.',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'votre.email@exemple.com'
                ]
            ])
            ->add('telephone', TelType::class, [
                'label' => 'Votre téléphone',
                'help' => 'Votre numéro restera confidentiel et sera utilisé uniquement par l\'administrateur pour détecter des faux commentaires.',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '01 23 45 67 89'
                ]
            ])
            ->add('rating', ChoiceType::class, [
                'label' => 'Votre note',
                'choices' => [
                    '⭐ 1 étoile' => 1,
                    '⭐⭐ 2 étoiles' => 2,
                    '⭐⭐⭐ 3 étoiles' => 3,
                    '⭐⭐⭐⭐ 4 étoiles' => 4,
                    '⭐⭐⭐⭐⭐ 5 étoiles' => 5,
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('commentaire', TextareaType::class, [
                'label' => 'Votre commentaire',
                'help' => 'Partagez votre expérience (minimum 10 caractères, maximum 1000 caractères)',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 5,
                    'placeholder' => 'Décrivez votre expérience avec ce professionnel...'
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Publier mon avis',
                'attr' => [
                    'class' => 'btn btn-primary btn-lg'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commentaire::class,
        ]);
    }
} 