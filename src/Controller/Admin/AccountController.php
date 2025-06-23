<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

#[Route('/admin/account')]
#[IsGranted('ROLE_ADMIN')]
class AccountController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    #[Route('/', name: 'admin_account')]
    public function index(): Response
    {
        return $this->render('admin/account/index.html.twig', [
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/change-username', name: 'admin_change_username')]
    public function changeUsername(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        
        $form = $this->createFormBuilder()
            ->add('username', TextType::class, [
                'label' => 'Nouveau nom d\'utilisateur',
                'data' => $user->getUsername(),
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le nom d\'utilisateur ne peut pas être vide']),
                    new Assert\Length(['min' => 3, 'max' => 180])
                ]
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Modifier le nom d\'utilisateur',
                'attr' => ['class' => 'btn btn-primary']
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newUsername = $form->get('username')->getData();
            
            // Vérifier si le username n'est pas déjà pris
            $existingUser = $this->entityManager->getRepository(User::class)
                ->findOneBy(['username' => $newUsername]);
            
            if ($existingUser && $existingUser->getId() !== $user->getId()) {
                $this->addFlash('error', 'Ce nom d\'utilisateur est déjà utilisé.');
            } else {
                $user->setUsername($newUsername);
                $this->entityManager->flush();
                $this->addFlash('success', 'Nom d\'utilisateur modifié avec succès !');
                
                return $this->redirectToRoute('admin_account');
            }
        }

        return $this->render('admin/account/change_username.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    #[Route('/change-password', name: 'admin_change_password')]
    public function changePassword(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        
        $form = $this->createFormBuilder()
            ->add('current_password', TextType::class, [
                'label' => 'Mot de passe actuel',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez saisir votre mot de passe actuel'])
                ]
            ])
            ->add('new_password', RepeatedType::class, [
                'type' => TextType::class,
                'first_options' => ['label' => 'Nouveau mot de passe'],
                'second_options' => ['label' => 'Confirmer le nouveau mot de passe'],
                'invalid_message' => 'Les mots de passe ne correspondent pas.',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le mot de passe ne peut pas être vide']),
                    new Assert\Length(['min' => 6, 'minMessage' => 'Le mot de passe doit contenir au moins 6 caractères'])
                ]
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Modifier le mot de passe',
                'attr' => ['class' => 'btn btn-primary']
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $currentPassword = $form->get('current_password')->getData();
            $newPassword = $form->get('new_password')->getData();
            
            // Vérifier le mot de passe actuel
            if (!$this->passwordHasher->isPasswordValid($user, $currentPassword)) {
                $this->addFlash('error', 'Le mot de passe actuel est incorrect.');
            } else {
                // Hasher et sauvegarder le nouveau mot de passe
                $hashedPassword = $this->passwordHasher->hashPassword($user, $newPassword);
                $user->setPassword($hashedPassword);
                $this->entityManager->flush();
                
                $this->addFlash('success', 'Mot de passe modifié avec succès !');
                return $this->redirectToRoute('admin_account');
            }
        }

        return $this->render('admin/account/change_password.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }
} 