<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Crée l\'utilisateur admin par défaut (admin/password)',
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private UserRepository $userRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Vérifier si l'admin existe déjà
        $existingAdmin = $this->userRepository->findOneBy(['username' => 'admin']);
        
        if ($existingAdmin) {
            $io->warning('L\'utilisateur admin existe déjà.');
            return Command::SUCCESS;
        }

        // Créer l'utilisateur admin
        $admin = new User();
        $admin->setUsername('admin');
        $admin->setRoles(['ROLE_ADMIN']);
        
        // Hasher le mot de passe "password"
        $hashedPassword = $this->passwordHasher->hashPassword($admin, 'password');
        $admin->setPassword($hashedPassword);

        // Sauvegarder
        $this->entityManager->persist($admin);
        $this->entityManager->flush();

        $io->success('Utilisateur admin créé avec succès !');
        $io->info('Username: admin');
        $io->info('Password: password');
        $io->warning('Pensez à changer le mot de passe depuis l\'interface d\'administration.');

        return Command::SUCCESS;
    }
} 