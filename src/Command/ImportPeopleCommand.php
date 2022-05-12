<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Command;

use App\Entity\DepartmentAffiliation;
use App\Entity\HistoricalEntity;
use App\Entity\Person;
use App\Entity\RoomAffiliation;
use App\Entity\SupervisorAffiliation;
use App\Entity\ThemeAffiliation;
use App\Repository\DepartmentRepository;
use App\Repository\MemberCategoryRepository;
use App\Repository\PersonRepository;
use App\Repository\RoomRepository;
use App\Repository\ThemeRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import-people',
    description: 'Add a short description for your command',
)]
class ImportPeopleCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly PersonRepository $personRepository,
        private readonly ThemeRepository $themeRepository,
        private readonly MemberCategoryRepository $categoryRepository,
        private readonly DepartmentRepository $departmentRepository,
        private readonly RoomRepository $roomRepository,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'host',
                'H',
                InputOption::VALUE_REQUIRED,
                'The host where the people database is located',
                '127.0.0.1'
            )
            ->addOption(
                'port',
                'P',
                InputOption::VALUE_REQUIRED,
                'The MySQL port where the people database is located',
                '3306'
            )
            ->addOption('database', 'd', InputOption::VALUE_REQUIRED, 'The name of the people database', 'people')
            ->addOption(
                'username',
                'u',
                InputOption::VALUE_REQUIRED,
                'The MySQL username to log in with. Must have select permission',
                'root'
            )
            ->addOption('password', 'p', InputOption::VALUE_OPTIONAL)
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Set this flag to replace all users in the database with those from the people database. All member info will be lost.'
            );
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
            default => $shortName
        };
    }

    private function translateMemberCategory(string $category): string
    {
        return match ($category) {
            'Faculty-Affiliate' => 'Affiliate',
            'Grad' => 'Graduate Student',
            'IGB Fellow' => 'Fellow',
            'IGB Staff' => 'Civil Service',
            'Theme Leader' => 'Faculty',
            'Undergrad' => 'Undergraduate Student',
            'AP - Research', 'Acad Professional', 'AP - Administration', 'Academic Professiona' => 'Academic Professional',
            'UIUC Visitor' => 'Visiting Scholar',
            'Core Advisor', 'Equipment User' => 'Non-IGB Member',
            default => $category
        };
    }

    private function translateDepartment(string $name): string
    {
        return match ($name) {
            'Veterinary Medicine' => 'Veterinary Clinical Medicine',
            'NCSA', 'Supercomputing Applications' => 'National Center for Supercomputing Applications',
            'School of Molecular & Cell Bio' => 'Molecular & Cellular Biology',
            'Natural Res & Env Sci' => 'Natural Resources & Environmental Sciences',
            'Molecular & Integrative Physl' => 'Molecular & Integrative Physiology',
            'Mechanical Sci & Engineering' => 'Mechanical Science & Engineering',
            'Materials Science & Engineerng' => 'Materials Science & Engineering',
            'Institute for Sustainability, Energy and Environme' => 'Institute for Sustainablility, Energy, and Environment',
            'Electrical & Computer Eng' => 'Electrical & Computer Engineering',
            'Ecology, Evolution, and Conservation Biology' => '	Evolution, Ecology, & Behavior',
            'Comparative Biosciences' => 'Comparative Sciences',
            'Chemical & Biomolecular Engr' => 'Chemical & Biomolecular Engineering',
            'Agricultural & Consumar Economics' => 'Agricultural & Consumer Economics',
            'Agricultural & Biological Engr' => 'Agricultural & Biological Engineering',
            default => $name
        };
    }

    private function isThemeLeader(int $type_id): bool
    {
        return $type_id == 11;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Gather options
        $io = new SymfonyStyle($input, $output);
        $host = $input->getOption('host');
        $port = $input->getOption('port');
        $database = $input->getOption('database');
        $username = $input->getOption('username');
        $password = $input->getOption('password');
        $force = $input->getOption('force');
        $hasPassword = $input->hasParameterOption(['-p', '--password']);

        if ($hasPassword && !$password) {
            $password = $io->askHidden('Input MySQL password for user ' . $username);
        }

        // Connect to People Database
        $dsn = "mysql:host=$host;port=$port;dbname=$database";
        $db = new \PDO($dsn, $username, $password);

        // Query People data
        $user_sql = "select users.*, phone.igb, phone.dept from users join user_theme on users.user_id=user_theme.user_id left join phone on users.user_id=phone.user_id where user_theme.theme_id!=0 group by users.user_id";
        $users = $db->query($user_sql)->fetchAll(); // Select users with theme info

        $theme_sql = "select * from themes";
        $themes = $db->query($theme_sql);

        $type_sql = "select * from type";
        $types = $db->query($type_sql);

        $department_sql = "select * from department";
        $departments = $db->query($department_sql);

//        $user_theme_sql = "select * from user_theme where theme_id!=0 and user_id!=0 and (start_date<>user_theme.end_date or start_date is null or end_date is null)"; // Fetch all relations where there is a theme
        $user_theme_sql = "select * from user_theme where theme_id!=0 and user_id!=0";
        $user_themes = $db->query($user_theme_sql);

//        $user_theme_sql = "select * from user_theme where theme_id!=0 and user_id!=0 and start_date=user_theme.end_date"; // Fetch all relations where there is a theme (no dates)
//        $user_themes_no_dates = $db->query($user_theme_sql); // the no-date ones have their dates in the users table

        $user_type_sql = "select * from user_theme where theme_id=0 and type_id!=0 and user_id!=0"; // This mostly covers people marked as "alumnus." What does this mean? This doesn't actually show up in the people database

        $user_room_sql = "select * from address where type='IGB'";
        $user_rooms = $db->query($user_room_sql);

        // Bookkeeping variables
        $personCount = 0;

        $usersById = [];
        $peopleById = [];
        $peopleByNetid = [];

        $themesById = [];
        $typesById = [];
        $departmentsById = [];
        $peopleDepartmentsById = [];

        // Parse themes
        $io->note("Correlating themes...");
        foreach ($themes as $theme) {
            $foundTheme = $this->themeRepository->findOneBy(
                ['shortName' => $this->translateTheme($theme['short_name'])]
            );
            if ($foundTheme) {
                $themesById[$theme['theme_id']] = $foundTheme;
            } else {
//                $io->warning("Theme not correlated: $theme[short_name]");
            }
        }

        // Parse member categories
        $io->note("Correlating member categories...");
        foreach ($types as $type) {
            $foundType = $this->categoryRepository->findOneBy(
                ['name' => $this->translateMemberCategory($type['name'])]
            );
            if ($foundType) {
                $typesById[$type['type_id']] = $foundType;
            } else {
//                $io->warning("Type not correlated: $type[name]");
            }
        }

        // Parse departments
        $io->note("Correlating departments...");
        foreach ($departments as $department) {
            $peopleDepartmentsById[$department['dept_id']] = $department;
            $foundDepartment = $this->departmentRepository->findOneBy(
                ['name' => $this->translateDepartment($department['name'])]
            );
            if ($foundDepartment) {
                $departmentsById[$department['dept_id']] = $foundDepartment;
            }
        }

        // First build up the basic people info
        $io->note("Building user list...");
        foreach ($users as $user) {
            if ($user['netid'] && isset($peopleByNetid[$user['netid']])) {
                // todo do something here to import the duplicate person
                $dummy = true;
            } else {
                if ($user['last_name']) { // only import the person if they actually have a name
                    $person = null;
                    if ($user['uin'] && is_int($user['uin'])) {
                        $person = $this->personRepository->findOneBy(['uin' => $user['uin']]);
                    }
                    if (!$person && $user['netid']) {
                        $person = $this->personRepository->findOneBy(['username' => $user['netid']]);
                    }
                    if (!$person) {
                        $person = new Person();
                    }
                    $person
                        ->setFirstName($user['first_name'])
                        ->setLastName($user['last_name'])
                        ->setEmail($user['email'])
                        ->setIsIgbTrainingComplete($user['safety_training'] == 1)
                        ->setIsDrsTrainingComplete($user['safety_training'] == 1); // todo import images somehow?
                    if ($user['netid']) {
                        $person
                            ->setNetid($user['netid'])
                            ->setUsername($user['netid']);
                    }
                    if ($user['uin'] && is_numeric($user['uin'])) {
                        $person->setUin($user['uin']);
                    }

                    if ($user['dept_id']) {
                        $departmentAffiliation = new DepartmentAffiliation();
                        if (isset($departmentsById[$user['dept_id']])) {
                            $departmentAffiliation
                                ->setDepartment($departmentsById[$user['dept_id']]);
                        } else {
                            $departmentAffiliation->setOtherDepartment(
                                $peopleDepartmentsById[$user['dept_id']]['name']
                            );
                        }
                        $person->addDepartmentAffiliation($departmentAffiliation);
                    }

                    if (preg_match('/^\\(217\\) (300|333|265|244)/u', $user['igb'])){
                        $person->setOfficePhone($user['igb']);
                    } elseif (preg_match('/^\\(217\\) (300|333|265|244)/u', $user['dept'])){
                        $person->setOfficePhone($user['dept']);
                    }

                    $peopleById[$user['user_id']] = $person;
                    $peopleByNetid[$user['netid']] = $person;
                    $usersById[$user['user_id']] = $user;
                    $personCount++;
                    $this->em->persist($person);
                }
            }
        }

        // Gather theme affiliations (w/ reasonable dates)
        $missingTypes = 0;
        $io->note("Building theme affiliations...");
        foreach ($user_themes as $user_theme) {
            if (isset($peopleById[$user_theme['user_id']]) && isset($themesById[$user_theme['theme_id']])) {
                if (isset($typesById[$user_theme['type_id']])) {
                    $themeAffiliation = (new ThemeAffiliation())
                        ->setTheme($themesById[$user_theme['theme_id']])
                        ->setMemberCategory($typesById[$user_theme['type_id']]);
                    $peopleById[$user_theme['user_id']]->addThemeAffiliation($themeAffiliation);

                    if($this->isThemeLeader($user_theme['type_id'])){
                        $themeAffiliation->setIsThemeLeader(true);
                    }

                    if ($user_theme['start_date'] == $user_theme['end_date']) {
                        $this->setDatesFromResult($usersById[$user_theme['user_id']], $themeAffiliation);
                    } else {
                        $this->setDatesFromResult($user_theme, $themeAffiliation);
                    }
                    $this->em->persist($themeAffiliation);
                } else {
                    $missingTypes++;
                }
            }
        }
        if ($missingTypes > 0) {
            $io->warning("Skipped $missingTypes affiliations due to missing member category");
        }

        // Next pass: set up supervisors
        $io->note("Building supervisor list...");
        foreach ($users as $user) {
            if ($user['supervisor_id'] && isset($peopleById[$user['supervisor_id']])) {
                $supervisorAffiliation = new SupervisorAffiliation();
                $peopleById[$user['user_id']]->addSupervisorAffiliation($supervisorAffiliation);
                $peopleById[$user['supervisor_id']]->addSuperviseeAffiliation($supervisorAffiliation);
                // todo need start and end dates (or at least end dates, when applicable)
//                $this->em->persist($supervisorAffiliation);
            }
        }

        // Set up room affiliations
        $io->note("Building room assignments...");
        foreach ($user_rooms as $user_room) {
            if (isset($peopleById[$user_room['user_id']])) {
                $roomNumbers = explode(',', $user_room['address2']);
                foreach ($roomNumbers as $roomNumber) {
                    $room = $this->roomRepository->findOneBy(['number' => trim($roomNumber)]);
                    if($room) {
                        $roomAffiliation = (new RoomAffiliation())
                            ->setRoom($room); // todo end dates
                        $peopleById[$user_room['user_id']]->addRoomAffiliation($roomAffiliation);

                        $this->setDatesFromResult($usersById[$user_room['user_id']], $roomAffiliation);
                    }
                }
            }
        }

        // Finish up
        $io->note("Saving to database...");
        $this->em->flush();
        $io->success("Imported $personCount people!");

        return Command::SUCCESS;
    }

    /**
     * @param array $from
     * @param HistoricalEntity $to
     * @return void
     */
    private function setDatesFromResult(array $from, $to): void
    {
        try {
            if ($from['start_date'] && $from['start_date'] != '0000-00-00') {
                $to->setStartedAt(new DateTimeImmutable($from['start_date']));
            }
            if ($from['end_date'] && $from['end_date'] != '0000-00-00') {
                $to->setEndedAt(new DateTimeImmutable($from['end_date']));
            }
        } catch (\Exception){

        }
    }
}
