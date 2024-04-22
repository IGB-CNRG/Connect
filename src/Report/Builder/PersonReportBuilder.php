<?php
/*
 * Copyright (c) 2024 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Report\Builder;

use App\Entity\Person;
use App\Report\Column\EmailColumn;
use App\Report\Column\EndedAtColumn;
use App\Report\Column\FirstNameColumn;
use App\Report\Column\LastNameColumn;
use App\Report\Column\MemberCategoryColumn;
use App\Report\Column\NetidColumn;
use App\Report\Column\StartedAtColumn;
use App\Report\Column\StatusColumn;
use App\Report\Column\ThemeColumn;
use App\Report\Column\UinColumn;
use App\Report\Column\UnitColumn;
use App\Report\Column\UsernameColumn;
use App\Report\Report\PersonReport;
use App\Repository\PersonRepository;
use App\Service\HistoricityManager;
use Symfony\Contracts\Service\Attribute\SubscribedService;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Service\ServiceSubscriberTrait;

class PersonReportBuilder implements ServiceSubscriberInterface
{
    use ServiceSubscriberTrait;
    private string $query = '';
    private string $sort = 'name';
    private string $sortDirection = 'asc';
    private array $themesToFilter = [];
    private array $typesToFilter = [];
    private array $rolesToFilter = [];
    private array $unitsToFilter = [];
    private bool $currentOnly = true;
    private bool $membersOnly = true;

    private bool $nameColumns = true;
    private bool $emailColumn = true;
    private bool $uinColumn = true;
    private bool $netidColumn = true;
    private bool $usernameColumn = false;
    private bool $unitColumn = true;
    private bool $statusColumn = true;
    private bool $themeColumns = true;
    private bool $memberCategoryColumns = true;
    private bool $startedAtColumns = true;
    private bool $endedAtColumns = true;

    public function getReport(): PersonReport
    {
        if ($this->membersOnly) {
            $qb = $this->personRepository()->createMembersOnlyIndexQueryBuilder();
        } else {
            $qb = $this->personRepository()->createIndexQueryBuilder();
        }
        if ($this->currentOnly) {
            $this->historicityManager()->addCurrentConstraint($qb, 'ta');
        }
        $this->personRepository()->addIndexFilters(
            $qb,
            $this->query,
            $this->sort,
            $this->sortDirection,
            $this->themesToFilter,
            $this->typesToFilter,
            $this->rolesToFilter,
            $this->unitsToFilter,
            $this->currentOnly
        );

        /** @var Person[] $people */
        $people = $qb->getQuery()->getResult();

        // look through $people and figure out how many theme columns we need
        $maxThemes = 0;
        foreach ($people as $person) {
            if($person->getThemeAffiliations()->count() > $maxThemes){
                $maxThemes = $person->getThemeAffiliations()->count();
            }
        }

        // build column list
        $columns = [];
        if($this->nameColumns){
            $columns[] = new LastNameColumn();
            $columns[] = new FirstNameColumn();
        }
        if($this->emailColumn){
            $columns[] = new EmailColumn();
        }
        if($this->uinColumn){
            $columns[] = new UinColumn();
        }
        if($this->netidColumn){
            $columns[] = new NetidColumn();
        }
        if($this->usernameColumn){
            $columns[] = new UsernameColumn();
        }
        if($this->unitColumn){
            $columns[] = new UnitColumn();
        }
        if($this->statusColumn){
            $columns[] = new StatusColumn();
        }

        for($i=0; $i<$maxThemes; $i++){
            if($this->themeColumns){
                $columns[] = new ThemeColumn($i+1);
            }
            if($this->memberCategoryColumns){
                $columns[] = new MemberCategoryColumn($i+1);
            }
            if($this->startedAtColumns){
                $columns[] = new StartedAtColumn($i+1);
            }
            if($this->endedAtColumns){
                $columns[] = new EndedAtColumn($i+1);
            }
        }

        return new PersonReport($people, $columns);
    }

    public function setQuery(string $query): self
    {
        $this->query = $query;

        return $this;
    }

    public function setSort(string $sort): self
    {
        $this->sort = $sort;

        return $this;
    }

    public function setSortDirection(string $sortDirection): self
    {
        $this->sortDirection = $sortDirection;

        return $this;
    }

    public function setThemesToFilter(array $themesToFilter): self
    {
        $this->themesToFilter = $themesToFilter;

        return $this;
    }

    public function setTypesToFilter(array $typesToFilter): self
    {
        $this->typesToFilter = $typesToFilter;

        return $this;
    }

    public function setRolesToFilter(array $rolesToFilter): self
    {
        $this->rolesToFilter = $rolesToFilter;

        return $this;
    }

    public function setUnitsToFilter(array $unitsToFilter): self
    {
        $this->unitsToFilter = $unitsToFilter;

        return $this;
    }

    public function setCurrentOnly(bool $currentOnly): self
    {
        $this->currentOnly = $currentOnly;

        return $this;
    }

    public function setMembersOnly(bool $membersOnly): self
    {
        $this->membersOnly = $membersOnly;

        return $this;
    }

    public function setNameColumns(bool $nameColumns): self
    {
        $this->nameColumns = $nameColumns;

        return $this;
    }

    public function setEmailColumn(bool $emailColumn): self
    {
        $this->emailColumn = $emailColumn;

        return $this;
    }

    public function setUinColumn(bool $uinColumn): self
    {
        $this->uinColumn = $uinColumn;

        return $this;
    }

    public function setNetidColumn(bool $netidColumn): self
    {
        $this->netidColumn = $netidColumn;

        return $this;
    }

    public function setUsernameColumn(bool $usernameColumn): self
    {
        $this->usernameColumn = $usernameColumn;

        return $this;
    }

    public function setUnitColumn(bool $unitColumn): self
    {
        $this->unitColumn = $unitColumn;

        return $this;
    }

    public function setStatusColumn(bool $statusColumn): self
    {
        $this->statusColumn = $statusColumn;

        return $this;
    }

    public function setThemeColumns(bool $themeColumns): self
    {
        $this->themeColumns = $themeColumns;

        return $this;
    }

    public function setMemberCategoryColumns(bool $memberCategoryColumns): self
    {
        $this->memberCategoryColumns = $memberCategoryColumns;

        return $this;
    }

    public function setStartedAtColumns(bool $startedAtColumns): self
    {
        $this->startedAtColumns = $startedAtColumns;

        return $this;
    }

    public function setEndedAtColumns(bool $endedAtColumns): self
    {
        $this->endedAtColumns = $endedAtColumns;

        return $this;
    }



    #[SubscribedService]
    private function personRepository(): PersonRepository
    {
        return $this->container->get(__CLASS__ . '::' . __FUNCTION__);
    }

    #[SubscribedService]
    private function historicityManager(): HistoricityManager
    {
        return $this->container->get(__CLASS__ . '::' . __FUNCTION__);
    }
}