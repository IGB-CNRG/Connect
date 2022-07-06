<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Command;

use App\Entity\Key;
use App\Entity\KeyAffiliation;
use App\Entity\Person;
use App\Repository\KeyRepository;
use App\Repository\PersonRepository;
use App\Repository\RoomRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import-assigned-keys',
    description: 'Add a short description for your command',
)]
class ImportAssignedKeysCommand extends Command
{
    private const KEY_COLUMN = 'G';
    private const KEY_COLUMNS = 11;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly RoomRepository $roomRepository,
        private readonly KeyRepository $keyRepository,
        private readonly PersonRepository $personRepository,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->addArgument('file', InputArgument::REQUIRED, 'Assigned Keys spreadsheet to import')
            ->addArgument('rows', InputArgument::REQUIRED, 'The maximum row to parse in the spreadsheet');;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $filename = $input->getArgument('file');
        $maxRow = $input->getArgument('rows');

        $io->note("Loading $filename...");

        $reader = new Xlsx();
        try {
            $spreadsheet = $reader->load($filename);
        } catch (Exception) {
            $io->error("Error loading given workbook");
            return Command::FAILURE;
        }

        try {
            $facultySheet = $spreadsheet->getSheet(0);
        } catch (Exception) {
            $io->error("No sheets in given workbook");
            return Command::FAILURE;
        }

        try {
            $firstColumnId = Coordinate::columnIndexFromString(self::KEY_COLUMN);
        } catch (Exception) {
            return Command::FAILURE;
        }

        $roomErrors = [];
        $assignedKeys = 0;
        for ($row = 3; $row <= $maxRow; $row++) {
            $uin = intval($facultySheet->getCell("C$row")->getValue());
            $netid = $facultySheet->getCell("D$row")->getValue();
            $firstName = $facultySheet->getCell("B$row")->getValue();
            $lastName = $facultySheet->getCell("A$row")->getValue();

            // get person
            $person = null;
            if ($uin) {
                $person = $this->personRepository->findOneBy(['uin' => $uin]);
            }
            if (!$person && $netid) {
                $person = $this->personRepository->findOneBy(['netid' => $netid]);
            }
            if (!$person && $lastName && $firstName) {
                $person = $this->personRepository->findOneBy(['lastName' => $lastName, 'firstName' => $firstName]);
            }
            if (!$person) {
                $person = (new Person())
                    ->setUin($uin)
                    ->setNetid($netid)
                    ->setFirstName($firstName)
                    ->setLastName($lastName);
                $this->em->persist($person);
            }

            $key = null;
            for ($i = $firstColumnId; $i < $firstColumnId + self::KEY_COLUMNS; $i++) {
                $cellValue = $facultySheet->getCell([$i, $row])->getValue();
                if ($cellValue) {
                    $room = $this->roomRepository->findOneBy(['number' => $cellValue]);

                    if (!$room) {
                        // Try to get the room by name instead
                        $room = $this->roomRepository->findOneBy(['name' => $cellValue]);
                    }
                    if ($room) {
                        // get the key corresponding to the given room
                        $keys = array_values(
                            $room->getCylinderKeys()->filter(function (Key $key) {
                                return $key->getRooms()->count() < 20; // Filter out master keys
                            })->toArray()
                        );
                        if (count($keys) > 0) {
                            $key = $keys[0];
                        } else {
                            $roomErrors[] = "$firstName $lastName: $cellValue";
                        }
                    } else {
                        // see if the key is named directly
                        $key = $this->keyRepository->findOneBy(['name' => $cellValue]);
                        if (!$key) {
                            $roomErrors[] = "$firstName $lastName: $cellValue";
                        }
                    }

                    if ($key) {
                        // check if the person has this key assignment already
                        $foundAssignment = false;
                        foreach ($person->getKeyAffiliations() as $affiliation) {
                            if ($affiliation->getCylinderKey() === $key) {
                                $foundAssignment = true;
                                break;
                            }
                        }

                        if (!$foundAssignment) {
                            // create the key affiliation
                            $keyAffiliation = (new KeyAffiliation())
                                ->setCylinderKey($key);
                            $person->addKeyAffiliation($keyAffiliation);
                            $this->em->persist($keyAffiliation);
                            $assignedKeys++;
                        }
                    }
                }
            }
        }

        if (count($roomErrors)) {
            $io->warning(count($roomErrors).' invalid rooms: ' . join("\n", $roomErrors));
        }

        $this->em->flush();

        $io->success("Found $assignedKeys key assignments!");

        return Command::SUCCESS;
    }
}
