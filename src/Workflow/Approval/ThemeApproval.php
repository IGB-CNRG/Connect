<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Workflow\Approval;

use App\Entity\Person;
use App\Entity\Theme;
use App\Entity\ThemeAffiliation;
use App\Enum\ThemeRole;
use App\Repository\PersonRepository;
use App\Service\HistoricityManager;

class ThemeApproval implements ApprovalStrategy
{
    public function __construct(
        private readonly PersonRepository $personRepository,
        private readonly HistoricityManager $historicityManager
    ) {}

    /**
     * @inheritDoc
     */
    public function getApprovers(Person $person): array
    {
        $themes = $this->currentThemes($person);
        $approvers = [];
        foreach ($themes as $theme) {
            array_merge($approvers, $this->personRepository->findByRoleInTheme($theme, ThemeRole::ThemeAdmin));
            array_merge($approvers, $this->personRepository->findByRoleInTheme($theme, ThemeRole::LabManager));
        }


        return $approvers;
    }

    public function getApprovalEmails(Person $person): array
    {
        $themes = $this->currentThemes($person);
        $emails = [];
        foreach ($themes as $theme) {
            $emails[] = $theme->getAdminEmail();
            $emails[] = $theme->getLabManagerEmail();
        }

        return $emails;
    }

    /**
     * @param Person $person
     * @return Theme[]
     */
    private function currentThemes(Person $person): array
    {
        return array_unique(
            array_map(fn(ThemeAffiliation $affiliation) => $affiliation->getTheme(),
                $this->historicityManager->getCurrentEntities($person->getThemeAffiliations())->toArray())
        );
    }
}