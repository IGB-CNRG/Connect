<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Command;

use App\Entity\Department;
use App\Entity\DepartmentAffiliation;
use App\Entity\HistoricalEntity;
use App\Entity\Person;
use App\Entity\RoomAffiliation;
use App\Entity\SupervisorAffiliation;
use App\Entity\Theme;
use App\Entity\ThemeAffiliation;
use App\Repository\DepartmentRepository;
use App\Repository\MemberCategoryRepository;
use App\Repository\PersonRepository;
use App\Repository\RoomRepository;
use App\Repository\ThemeRepository;
use App\Service\ActivityLogger;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PDO;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[AsCommand(
    name: 'app:import-people',
    description: 'Add a short description for your command',
)]
class ImportPeopleCommand extends Command
{
    private PDO $db;
    private ConsoleSectionOutput $subProgressSection;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly PersonRepository $personRepository,
        private readonly ThemeRepository $themeRepository,
        private readonly MemberCategoryRepository $categoryRepository,
        private readonly DepartmentRepository $departmentRepository,
        private readonly RoomRepository $roomRepository,
        private readonly ActivityLogger $logger,
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
     * @param array $usersById
     * @param Person[] $peopleById
     * @return void
     */
    protected function createSupervisorAffiliations(array $usersById, array $peopleById): void
    {
        foreach ($usersById as $user) {
            if ($user['supervisor_id'] && isset($peopleById[$user['supervisor_id']])
                && isset($peopleById[$user['user_id']])) {
                $supervisorAffiliation = new SupervisorAffiliation();
                $peopleById[$user['user_id']]->addSupervisorAffiliation($supervisorAffiliation);
                $peopleById[$user['supervisor_id']]->addSuperviseeAffiliation($supervisorAffiliation);
                $supervisorAffiliation->setStartedAt($peopleById[$user['user_id']]->getStartedAt())->setEndedAt(
                    $peopleById[$user['user_id']]->getEndedAt()
                );
            }
        }
    }

    /**
     * @param Department[] $departmentsById
     * @param array $otherDepartmentsById
     * @param ThemeAffiliation[][] $validThemeAffiliations
     * @return array{Person[], array}
     */
    protected function getPeople(
        array $departmentsById,
        array $otherDepartmentsById,
        array $validThemeAffiliations
    ): array {
        $peopleById = [];
        $peopleByNetid = [];
        $usersById = $this->getUsers();
        $progress = new ProgressBar($this->subProgressSection, count($usersById));
        $progress->setFormat('custom');
        foreach ($usersById as $user) {
            $progress->setMessage(sprintf('Importing %s', $user['netid']));
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
                    if (isset($validThemeAffiliations[$user['user_id']])
                        && count(
                               $validThemeAffiliations[$user['user_id']]
                           ) > 0) {
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
                                    $otherDepartmentsById[$user['dept_id']]['name']
                                );
                            }
                            $person->addDepartmentAffiliation($departmentAffiliation);
                        }

                        if (preg_match('/^\\(217\\) (300|333|265|244)/u', $user['igb'])) {
                            $person->setOfficePhone($user['igb']);
                        } elseif (preg_match('/^\\(217\\) (300|333|265|244)/u', $user['dept'])) {
                            $person->setOfficePhone($user['dept']);
                        }

                        $peopleById[$user['user_id']] = $person;
                        $peopleByNetid[$user['netid']] = $person;
                        $usersById[$user['user_id']] = $user;
                        $this->em->persist($person);

                        // persist theme affiliations
                        foreach ($validThemeAffiliations[$user['user_id']] as $themeAffiliation) {
                            $person->addThemeAffiliation($themeAffiliation);
                            $this->em->persist($themeAffiliation);
                        }

                        // create an initial log entry
                        $this->logger->logPersonActivity($person, 'Imported from IGB People Database');
                    }
                }
            }
            $progress->advance();
        }
        $this->subProgressSection->clear();
        return array($peopleById, $usersById);
    }

    /**
     * @return array
     */
    protected function getUsers(): array
    {
        // Query People data
        $user_sql = "select users.*, phone.igb, phone.dept from users left join phone on users.user_id=phone.user_id group by users.user_id";
        $users = $this->db->query($user_sql);

        $usersById = [];
        foreach ($users as $user) {
            $usersById[$user['user_id']] = $user;
        }

        return $usersById; // Select users
    }

    /**
     * @return Theme[]
     */
    protected function getThemesById(): array
    {
        $theme_sql = "select * from themes";
        $themes = $this->db->query($theme_sql);

        $themesById = [];

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
        return $themesById;
    }

    /**
     * @param array $themesById
     * @param array $typesById
     * @return ThemeAffiliation[][]
     */
    protected function getValidThemeAffiliations(
        array $themesById,
        array $typesById
    ): array {
        $user_theme_sql = "select user_theme.*, users.start_date as user_start, users.end_date as user_end from user_theme left join users on user_theme.user_id=users.user_id where theme_id!=0 and users.user_id!=0";
        $user_themes = $this->db->query($user_theme_sql);

        /** @var ThemeAffiliation[][] $validThemeAffiliations */
        $validThemeAffiliations = [];

        $missingTypes = 0;
        $progress = new ProgressBar($this->subProgressSection, $user_themes->rowCount());
        $progress->setFormat('custom');
        $progress->setMessage('Processing affiliations');
        foreach ($user_themes as $user_theme) {
            if (isset($themesById[$user_theme['theme_id']])) {
                if (isset($typesById[$user_theme['type_id']])) {
                    $themeAffiliation = (new ThemeAffiliation())
                        ->setTheme($themesById[$user_theme['theme_id']])
                        ->setMemberCategory($typesById[$user_theme['type_id']]);

                    if ($this->isThemeLeader($user_theme['type_id'])) {
                        $themeAffiliation->setIsThemeLeader(true);
                    }

                    if ($user_theme['start_date'] == $user_theme['end_date']) {
                        $this->setDatesFromResult(
                            ['start_date' => $user_theme['user_start'], 'end_date' => $user_theme['user_end']],
                            $themeAffiliation
                        );
                    } else {
                        $this->setDatesFromResult($user_theme, $themeAffiliation);
                    }
                    // todo add some test to determine if this affiliation is "valid"
                    if (!isset($validThemeAffiliations[$user_theme['user_id']])) {
                        $validThemeAffiliations[$user_theme['user_id']] = [];
                    }
                    $validThemeAffiliations[$user_theme['user_id']][] = $themeAffiliation;
                } else {
                    $missingTypes++;
                }
            }
            $progress->advance();
        }

        // Combine any overlapping theme affiliations to try to remove some noise from the people database data
        $progress->start(count($validThemeAffiliations));
        $progress->setMessage('Combining overlapping theme affiliations');
        foreach ($validThemeAffiliations as $user_id => $themeAffiliations) {
            $toDelete = [];
            for ($i1 = 0; $i1 < count($themeAffiliations); $i1++) {
                for ($i2 = $i1 + 1; $i2 < count($themeAffiliations); $i2++) {
                    if (!in_array($i1, $toDelete) && !in_array($i2, $toDelete)) {
                        $affiliation1 = $themeAffiliations[$i1];
                        $affiliation2 = $themeAffiliations[$i2];
                        // If a theme affiliation corresponds to the same theme with the same member type, combine it
                        if ($affiliation1->getTheme() === $affiliation2->getTheme()
                            && $affiliation1->getMemberCategory() === $affiliation2->getMemberCategory()
                            && $affiliation1->overlaps($affiliation2)) {
                            // merge the dates
                            if (($affiliation2->getStartedAt() === null
                                 || $affiliation2->getStartedAt() <= $affiliation1->getStartedAt())
                                && $affiliation1->getStartedAt() !== null) {
                                $affiliation1->setStartedAt($affiliation2->getStartedAt());
                            }
                            if (($affiliation2->getEndedAt() === null
                                 || $affiliation2->getEndedAt() >= $affiliation1->getEndedAt())
                                && $affiliation1->getEndedAt() !== null) {
                                $affiliation1->setEndedAt($affiliation2->getEndedAt());
                            }
                            if ($affiliation2->getIsThemeLeader()) {
                                $affiliation1->setIsThemeLeader(true);
                            }
                            // mark the extraneous affiliation for removal
                            $toDelete[] = $i2;
                        }
                    }
                }
            }
            // remove all marked affiliations
            if (count($toDelete) > 0) {
                foreach ($toDelete as $deleteId) {
                    unset($themeAffiliations[$deleteId]);
                }
                $validThemeAffiliations[$user_id] = array_values($themeAffiliations);
            }
            $progress->advance();
        }

        $this->subProgressSection->clear();
        return $validThemeAffiliations;
    }


    /**
     * @return array
     */
    protected function parseDepartments(): array
    {
        $department_sql = "select * from department";
        $departments = $this->db->query($department_sql);
        $departmentsById = [];
        $peopleDepartmentsById = [];
        foreach ($departments as $department) {
            $peopleDepartmentsById[$department['dept_id']] = $department;
            $foundDepartment = $this->departmentRepository->findOneBy(
                ['name' => $this->translateDepartment($department['name'])]
            );
            if ($foundDepartment) {
                $departmentsById[$department['dept_id']] = $foundDepartment;
            }
        }
        return array($departmentsById, $peopleDepartmentsById);
    }

    /**
     * @return array
     */
    protected function parseMemberCategories(): array
    {
        $type_sql = "select * from type";
        $types = $this->db->query($type_sql);
        $typesById = [];
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
        return $typesById;
    }

    /**
     * @param Person[] $peopleById
     * @param array $usersById
     * @return void
     */
    protected function setUpRoomAffiliations(array $peopleById, array $usersById): void
    {
        $user_room_sql = "select * from address where type='IGB'";
        $user_rooms = $this->db->query($user_room_sql);
        foreach ($user_rooms as $user_room) {
            if (isset($peopleById[$user_room['user_id']])) {
                $roomNumbers = explode(',', $user_room['address2']);
                foreach ($roomNumbers as $roomNumber) {
                    $room = $this->roomRepository->findOneBy(['number' => trim($roomNumber)]);
                    if ($room) {
                        $roomAffiliation = (new RoomAffiliation())
                            ->setRoom($room);
                        $peopleById[$user_room['user_id']]->addRoomAffiliation($roomAffiliation);

                        $roomAffiliation->setStartedAt($peopleById[$user_room['user_id']]->getStartedAt())
                            ->setEndedAt($peopleById[$user_room['user_id']]->getEndedAt());
                    }
                }
            }
        }
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
        if (!$output instanceof ConsoleOutputInterface) {
            throw new \LogicException('This command accepts only an instance of "ConsoleOutputInterface".');
        }
        // Gather options
        $section1 = $output->section();
        $section2 = $output->section();
        $this->subProgressSection = $output->section();
        $io = new SymfonyStyle($input, $section1);
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

        ProgressBar::setFormatDefinition('custom', "%message%...\n%current%/%max% %bar% %percent%%");
        $progress = new ProgressBar($section2, 10);
        $progress->setFormat('custom');

        $progress->start();

        // Connect to People Database
        $progress->setMessage("Connecting to people database");
        $dsn = "mysql:host=$host;port=$port;dbname=$database";
        $this->db = new \PDO($dsn, $username, $password);
        $progress->advance();

        // Parse themes
        $progress->setMessage('Correlating themes');
        $progress->display();
        $themesById = $this->getThemesById();
        $progress->advance();

        // Parse member categories
        $progress->setMessage('Correlating member categories');
        $progress->display();
        $typesById = $this->parseMemberCategories();
        $progress->advance();

        // Parse departments
        $progress->setMessage("Correlating departments");
        $progress->display();
        list($departmentsById, $otherDepartmentsById) = $this->parseDepartments();
        $progress->advance();

        // Gather theme affiliations (w/ reasonable dates)
        $progress->setMessage('Gathering valid theme affiliations');
        $progress->display();
        $validThemeAffiliations = $this->getValidThemeAffiliations(
            $themesById,
            $typesById
        );
        $progress->advance();

        // First build up the basic people info
        $progress->setMessage('Importing valid users');
        $progress->display();
        list($peopleById, $usersById) = $this->getPeople(
            $departmentsById,
            $otherDepartmentsById,
            $validThemeAffiliations
        );
        $progress->advance();

        // Next pass: set up supervisors
        $progress->setMessage('Building supervisor relationships');
        $progress->display();
        $this->createSupervisorAffiliations($usersById, $peopleById);
        $progress->advance();

        // Set up room affiliations
        $progress->setMessage('Building room assignments');
        $progress->display();
        $this->setUpRoomAffiliations($peopleById, $usersById);
        $progress->advance();

        // Finish up
        $progress->setMessage('Saving to database');
        $progress->display();
        $this->em->flush();
        $progress->advance();

        $progress->setMessage('Importing images');
        foreach ($peopleById as $userId => $person) {
            $user = $usersById[$userId];
            if ($user['image_location']) {
                try {
                    // Grab the 'large' image
                    $image = new UploadedFile(
                        'people_images/users/' . str_replace(".", "_large.", $user['image_location']),
                        $user['image_location'],
                        null,
                        null,
                        true
                    );
                    $person->setImageFile($image);
                    $this->em->persist($person);
                } catch (FileException $e) {
                }
            }
        }
        $this->em->flush();
        $progress->setMessage("Done");
        $progress->finish();

        $io->success(sprintf("Imported %d people!", count($peopleById)));

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
        } catch (\Exception) {
        }
    }
}
