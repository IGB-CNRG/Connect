<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Command;

use App\Entity\ThemeAffiliation;
use App\Repository\ThemeAffiliationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import-end-dates',
    description: 'Add a short description for your command',
)]
class ImportEndDatesCommand extends Command
{
    public function __construct(
        private readonly ThemeAffiliationRepository $repository,
        private readonly EntityManagerInterface $entityManager,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->addArgument('file', InputArgument::OPTIONAL, 'Argument description');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $filename = $input->getArgument('file');

        $found = 0;

        $file = fopen($filename, 'r');
        while (($line = fgetcsv($file)) !== false) {
            $firstName = $line[0];
            $lastName = $line[1];
            $email = $line[2];
            $endDate = $line[3];
            $theme = $line[4];
            $startDate = $line[5];
            $reason = $line[6];
            $io->text("Searching $firstName $lastName, $email, $theme, $startDate");
            /** @var ThemeAffiliation $affiliation */
            $affiliation = $this->repository->createQueryBuilder('a')
                ->leftJoin('a.person', 'p')
                ->leftJoin('a.theme', 't')
                ->andWhere('p.email = :email')
                ->andWhere('a.startedAt = :start')
                ->andWhere('t.shortName = :theme')
                ->andWhere('a.endedAt is null')
                ->setParameter('email', $email)
                ->setParameter('start', $startDate)
                ->setParameter('theme', $this->translateTheme($theme))
                ->getQuery()
                ->getOneOrNullResult();

            if ($affiliation !== null) {
                $found++;
                $io->text("Found {$affiliation->getTheme()}, {$affiliation->getPerson()}");

                $affiliation->setEndedAt(new \DateTimeImmutable($endDate))
                    ->setExitReason($reason);
                $this->entityManager->persist($affiliation);
            }
        }

        $this->entityManager->flush();

        $io->success("Found $found affiliations to update");

        return Command::SUCCESS;
    }

    /**
     * Translates between some themes that are in the people database and themes that are in CONNECT. This allows for
     *  extra distinctions the people database makes that we do not, different spellings, and defunct themes.
     * @param string $shortName
     * @return string
     */
    private function translateTheme(string $shortName): string
    {
        return match ($shortName) {
            'ADMIN' => 'ADM',
            'GBB' => 'GNDP',
            'ReBTE' => 'RBTE',
            'RIPE' => 'GEGC',
            'BIOTECH', 'HPCBio' => 'CBC',
            default => $shortName
        };
    }
}
