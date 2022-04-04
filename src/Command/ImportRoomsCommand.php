<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Command;

use App\Entity\Room;
use App\Repository\RoomRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import-rooms',
    description: 'Import rooms. Expects a CSV file with one column containing room number.',
)]
class ImportRoomsCommand extends Command
{
    public function __construct(private EntityManagerInterface $em, private RoomRepository $roomRepository)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('file', InputArgument::REQUIRED, 'CSV file containing room information');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $filename = $input->getArgument('file');

        $numNew = 0;
        $numDuplicates = 0;
        $usedNumbers = [];

        $file = fopen($filename, 'r');
        while (($line = fgetcsv($file)) !== false) {
            if (($number = trim($line[0])) !== "") {
                if (!in_array($number, $usedNumbers)
                    && $this->roomRepository->findOneBy(['number' => $number]) === null) {
                    $room = (new Room())
                        ->setNumber($number);
                    $this->em->persist($room);
                    $numNew++;
                } else {
                    $numDuplicates++;
                }
                $usedNumbers[] = $number;
            }
        }

        $this->em->flush();
        $io->success(sprintf('Added %d new rooms. Skipped %d duplicates.', $numNew, $numDuplicates));

        return Command::SUCCESS;
    }
}
