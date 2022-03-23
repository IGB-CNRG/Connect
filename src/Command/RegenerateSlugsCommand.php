<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Command;

use App\Repository\PersonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:regenerate-slugs',
    description: 'Add a short description for your command',
)]
class RegenerateSlugsCommand extends Command
{
    public function __construct(
        public PersonRepository $personRepository,
        public EntityManagerInterface $em,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $people = $this->personRepository->findAll();
        foreach($people as $person){
            $person->setSlug(null);
            $this->em->persist($person);
        }
        $this->em->flush();

        $io->success(count($people).' slugs generated!');

        return Command::SUCCESS;
    }
}
