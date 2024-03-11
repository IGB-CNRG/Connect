<?php
/*
 * Copyright (c) 2024 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Twig\Runtime;

use App\Entity\HistoricalEntityInterface;
use App\Entity\Person;
use App\Entity\SupervisorAffiliation;
use App\Entity\Theme;
use App\Entity\ThemeAffiliation;
use App\Entity\ThemeRole;
use App\Repository\PersonRepository;
use App\Repository\ThemeRoleRepository;
use App\Service\HistoricityManager;
use DateTimeInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ReadableCollection;
use Twig\Extension\RuntimeExtensionInterface;

class ConnectRuntime implements RuntimeExtensionInterface
{

    public function __construct(
        private readonly HistoricityManager $historicityManager,
        private readonly PersonRepository $personRepository,
        private readonly ThemeRoleRepository $roleRepository
    ) {
    }

    public function getCurrent(Collection $collection): ReadableCollection
    {
        return $this->historicityManager->getCurrentEntities($collection);
    }

    public function getCurrentAndFuture(Collection $collection): ReadableCollection
    {
        return $this->historicityManager->getCurrentAndFutureEntities($collection);
    }

    public function getMember(Collection $collection): ReadableCollection
    {
        return $collection->filter(function (ThemeAffiliation $themeAffiliation) {
            return !$themeAffiliation->getTheme()->getIsOutsideGroup();
        });
    }

    public function getRoleName(string $rawRole): string
    {
        $roleNames = array_flip(Person::USER_ROLES);
        if (key_exists($rawRole, $roleNames)) {
            return $roleNames[$rawRole];
        } else {
            return $rawRole;
        }
    }

    /**
     * Filters a collection of SupervisorAffiliations that match the given theme
     * @param Collection $collection
     * @param Theme $theme
     * @return ReadableCollection
     */
    public function filterByTheme(Collection $collection, Theme $theme): ReadableCollection
    {
        return $collection->filter(function (SupervisorAffiliation $affiliation) use ($theme) {
            return $affiliation->getSuperviseeThemeAffiliation()->getTheme() === $theme;
        });
    }

    /**
     * @param Collection|HistoricalEntityInterface[] $entities
     * @return DateTimeInterface|null
     */
    public function earliest(Collection|array $entities): ?DateTimeInterface
    {
        if ($entities instanceof Collection) {
            $entities = $entities->toArray();
        }

        return $this->historicityManager->getEarliest($entities);
    }

    /**
     * @param Collection|HistoricalEntityInterface[] $entities
     * @return DateTimeInterface|null
     */
    public function latest(Collection|array $entities): ?DateTimeInterface
    {
        if ($entities instanceof Collection) {
            $entities = $entities->toArray();
        }

        return $this->historicityManager->getLatest($entities);
    }

    /**
     * Takes a tab-delimited table and space-pads it to align correctly
     * @param string $tableString
     * @return string
     */
    public function formatPlainTextTable(string $tableString): string
    {
        $rows = explode("\n", $tableString);
        $table = [];
        foreach ($rows as $row) {
            if (trim($row) !== '') {
                $table[] = explode("\t", trim($row));
            }
        }

        $numColumns = count($table[0]);
        $numRows = count($table);
        for ($col = 0; $col < $numColumns; $col++) {
            // Find the max length of each column
            $maxColumnLength = 0;
            for ($row = 0; $row < $numRows; $row++) {
                $value = $table[$row][$col] ?? '';
                $maxColumnLength = max($maxColumnLength, strlen($value));
            }

            // Pad each cell in the column to that length + 2
            for ($row = 0; $row < $numRows; $row++) {
                $value = $table[$row][$col] ?? '';
                $table[$row][$col] = str_pad($value, $maxColumnLength + 2, " ", STR_PAD_RIGHT);
            }
        }

        // Re-join the table
        $rows = [];
        foreach ($table as $row) {
            $rows[] = join("", $row);
        }

        return join("\n", $rows);
    }

    /**
     * @param Theme $theme
     * @return Collection|ThemeAffiliation[]
     */
    public function getThemeRoles(Theme $theme): Collection|array
    {
        // todo this is duplicated in ThemeController. Is there a more central place we can put this?
        return array_map(
            fn(ThemeRole $role) => [
                'name' => $role->getName(),
                'people' => $this->personRepository->findByRoleInTheme($theme, $role),
            ],
            $this->roleRepository->findAll()
        );
    }
}