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
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getApprovers(Person $person): array
    {
        $themes = $this->currentAndFutureThemes($person);
        $approvers = [];
        foreach ($themes as $theme) {
            $approvers = array_merge(
                $approvers,
                $this->personRepository->findApproversInTheme($theme), // Approvers by theme role
                $theme->getApprovers()->toArray() // Additional assigned approvers
            );
        }

        if (count($approvers) === 0) {
            // Add HR people if there are no approvers found
            $approvers = $this->personRepository->findByRole('ROLE_HR');
        }

        $approvers = array_unique($approvers);
        usort($approvers, function (Person $a, Person $b) {
            return ($a->getLastName() <=> $b->getLastName()) === 0
                ? ($a->getFirstName() <=> $b->getFirstName())
                : ($a->getLastName() <=> $b->getLastName());
        });

        return $approvers;
    }

    public function getApprovalEmails(Person $person): array
    {
        return array_map(fn(Person $approver) => $approver->getEmail(), $this->getApprovers($person));
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