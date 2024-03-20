<?php
/*
 * Copyright (c) 2024 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Workflow\Approval;

use App\Entity\Person;
use App\Entity\Theme;
use App\Entity\ThemeAffiliation;
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
        $themes = $this->currentAndFutureThemes($person);
        $approvers = [];
        foreach ($themes as $theme) {
            $approvers = array_merge($approvers, $this->personRepository->findApproversInTheme($theme));
        }


        return $approvers;
    }

    public function getApprovalEmails(Person $person): array
    {
        $themes = $this->currentAndFutureThemes($person);
        $emails = [];
        foreach ($themes as $theme) {
            if($theme->getAdminEmail()) {
                $emails[] = $theme->getAdminEmail();
            }
            if($theme->getLabManagerEmail()) {
                $emails[] = $theme->getLabManagerEmail();
            }
        }

        return $emails;
    }

    /**
     * @param Person $person
     * @return Theme[]
     */
    private function currentAndFutureThemes(Person $person): array
    {
        return array_unique(
            array_map(fn(ThemeAffiliation $affiliation) => $affiliation->getTheme(),
                $this->historicityManager->getCurrentAndFutureEntities($person->getThemeAffiliations())->toArray())
        );
    }
}