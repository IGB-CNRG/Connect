<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Command;

use App\Entity\Person;
use App\Workflow\Membership;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:initialize-admin-user',
    description: 'Create an admin user',
)]
class InitializeAdminUserCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $em, string $name = null)
    {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->addArgument('username', InputArgument::REQUIRED, 'Admin username to create')
            ->addArgument('firstName', InputArgument::REQUIRED, 'Admin first name')
            ->addArgument('lastName', InputArgument::REQUIRED, 'Admin last name');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $username = $input->getArgument('username');
        $firstname = $input->getArgument('firstName');
        $lastname = $input->getArgument('lastName');

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion(
            sprintf(
                'This operation will create a user with username %s so you can log in and complete setup. Only run this command if the database has not yet been initialized. Continue? (Y/n) ',
                $username
            ), true
        );
        if (!$helper->ask($input, $output, $question)) {
            return Command::SUCCESS;
        }

        // Create admin user
        $user = (new Person())
            ->setUsername($username)
            ->setFirstName($firstname)
            ->setLastName($lastname)
            ->setRoles(['ROLE_ADMIN'])
            ->setMembershipStatus(Membership::PLACE_ACTIVE)
            ->setMembershipUpdatedAt(new \DateTimeImmutable());
        $this->em->persist($user);

        $this->em->flush();

        $io->success(
            'Setup complete! Please log in to CONNECT with your username and your IGB password.'
        );

        return Command::SUCCESS;
    }
}
