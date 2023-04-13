<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:install',
    description: 'Run all installation commands',
)]
class InstallCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->addArgument('config', InputArgument::REQUIRED, 'Config file')
            ->addOption('skip-to', 's', InputOption::VALUE_OPTIONAL, 'Specify a step to skip to (create-database, initialize-admin-user, import-sql, import-people, import-faculty)', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $configPath = $input->getArgument('config');
        $skipTo = $input->getOption('skip-to');
        $skipImportFaculty = false;
        $skipImportPeople = $skipImportFaculty || $skipTo === 'import-faculty';
        $skipImportSql = $skipImportPeople || $skipTo === 'import-people';
        $skipFirstRun = $skipImportSql || $skipTo === 'import-sql';
        $skipCreateDatabase = $skipFirstRun || $skipTo === 'initialize-admin-user';
        $skipPrecheck = $skipCreateDatabase || $skipTo === 'create-database';

        $config = yaml_parse_file($configPath);
        if ($config === false) {
            $io->error("Error reading file '{$configPath}'");

            return Command::FAILURE;
        }

        // check if the user has run the initial setup
        if (!$skipPrecheck) {
            $io->title('Pre-check');
            $question = new ConfirmationQuestion(
                "Before installation, please ensure you have created the .env.local configuration file and run 'composer install'. See doc/INSTALL.md for more details.\nAre you ready to continue?"
            );
            if (!$io->askQuestion($question)) {
                $io->note('Exiting without installation.');

                return Command::SUCCESS;
            }
        }

        // create database
        if(!$skipCreateDatabase) {
            $io->title('Creating Database Structure');
            $databaseCommand = $this->getApplication()->find('doctrine:migrations:migrate');
            $databaseInput = new ArrayInput([]);
            $databaseCommand->run($databaseInput, $output);
        }

        if(!$skipFirstRun) {
            $io->title('Running initialize-admin-user command');
            $firstRunCommand = $this->getApplication()->find('app:initialize-admin-user');
            $firstRunInput = new ArrayInput([
                'username' => $config['initialize-admin-user']['username'],
                'firstName' => $config['initialize-admin-user']['first-name'],
                'lastName' => $config['initialize-admin-user']['last-name'],
            ]);
            $firstRunCommand->run($firstRunInput, $output);
        }

        // load sql
        if(!$skipImportSql) {
            $io->title('Loading sql file');
            $this->runSqlFile($config['import-sql']);
        }

        // import people database
        if(!$skipImportPeople) {
            $io->title('Importing People Database');
            $importPeopleCommand = $this->getApplication()->find('app:import-people');
            $importPeopleInput = new ArrayInput([
                '--host' => $config['import-people']['host'],
                '--port' => $config['import-people']['port'],
                '--database' => $config['import-people']['database'],
                '--username' => $config['import-people']['username'],
                '--password' => $config['import-people']['password'],
            ]);
            $importPeopleCommand->run($importPeopleInput, $output);
        }

        // import faculty spreadsheet
        if(!$skipImportFaculty) {
            $io->title('Importing Faculty Master List');
            $importFacultySpreadsheetCommand = $this->getApplication()->find('app:import-faculty-spreadsheet');
            $importFacultySpreadsheetInput = new ArrayInput([
                'file' => $config['import-faculty']['file'],
                'rows' => $config['import-faculty']['rows'],
            ]);
            $importFacultySpreadsheetCommand->run($importFacultySpreadsheetInput, $output);
        }

        $io->success('Installation succeeded');

        return Command::SUCCESS;
    }

    /**
     * @throws Exception
     */
    private function runSqlFile($file): void
    {
        $db = $this->entityManager->getConnection()->getNativeConnection();
        if (file_exists($file)) {
            $sql = trim(file_get_contents($file));
            if ($sql) {
                $db->beginTransaction();
                $count = 0;
                try {
                    $querySet = $db->prepare($sql);
                    $querySet->execute();
                    while ($querySet->nextRowSet()) {
                        ++$count;
                    }
                } catch (Exception $e) {
                    $db->rollBack();
                    throw $e;
                }
                echo "{$file}: {$count}\n";
            } else {
                throw new Exception("File {$file} appears to be empty.");
            }
        } else {
            throw new Exception("File '{$file}' not found.");
        }
    }
}
