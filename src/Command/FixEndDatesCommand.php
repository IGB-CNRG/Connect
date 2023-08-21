<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Command;

use App\Entity\ThemeAffiliation;
use App\Repository\PersonRepository;
use App\Service\HistoricityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:fix-end-dates',
    description: 'Add a short description for your command',
)]
class FixEndDatesCommand extends Command
{
    public function __construct(
        private readonly PersonRepository $repository,
        private readonly HistoricityManager $historicityManager,
        private readonly EntityManagerInterface $entityManager,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $unaffiliated = 0;
        $updated = 0;

        $allPeople = $this->repository->findAll();
        foreach ($allPeople as $person) {
            if ($person->getThemeAffiliations()->count() === 0) {
                $io->text("$person has no affiliations");
                $unaffiliated++;
                $this->entityManager->remove($person);
            } else {
                $endDate = array_reduce(
                    $person->getThemeAffiliations()->toArray(),
                    function ($carry, ThemeAffiliation $affiliation) {
                        if ($carry === null || $affiliation->getEndedAt() === null) {
                            return null;
                        }
                        if ($carry > $affiliation->getEndedAt()) {
                            return $carry;
                        }

                        return $affiliation->getEndedAt();
                    },
                    new \DateTimeImmutable('2001-01-01')
                );
                if ($endDate !== null) {
                    if ($this->historicityManager->endAffiliations(
                        array_merge(
                            $person->getSuperviseeAffiliations()->toArray(),
                            $person->getSponseeAffiliations()->toArray(),
                            $person->getRoomAffiliations()->toArray(),
                            $person->getSupervisorAffiliations(),
                            $person->getSponsorAffiliations()
                        ),
                        $endDate
                    )) {
                        $io->text("$person, ended at {$endDate->format('n/j/Y')}");
                        $updated++;
                    }
                }
            }
        }
        $this->entityManager->flush();
        $io->success("Removed $unaffiliated people without information. Updated $updated people's affiliations.");

        return Command::SUCCESS;
    }
}
