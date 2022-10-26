<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Service;

use App\Entity\Person;
use App\Entity\Workflow\PersonEntryWorkflowProgress;
use App\Entity\Workflow\PersonWorkflowProgress;
use App\Enum\ThemeRole;
use App\Enum\Workflow\WorkflowApproval;
use App\Repository\PersonEntryWorkflowProgressRepository;
use App\Repository\PersonRepository;
use Symfony\Contracts\Service\Attribute\SubscribedService;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Service\ServiceSubscriberTrait;

// todo so far this class is only for Person Workflows. We'll figure out how to generalize it later. Or maybe not.
class WorkflowManager implements ServiceSubscriberInterface
{
    use ServiceSubscriberTrait, EntityManagerAware;

    /**
     * @param Person $user
     * @return PersonEntryWorkflowProgress[]
     */
    public function findMyApprovals(Person $user): array
    {
        $entryProgresses = $this->personEntryWorkflowProgressRepository()->findByApprover($user);
        $approvals = [];
        foreach ($entryProgresses as $progress) {
            if (key_exists($progress->getStage(), $approvals)) {
            }
        }
        return $approvals;
    }

    public function submitForApproval(PersonWorkflowProgress $progress)
    {
        $stage = $progress->getStage();
        $progress->clearApprovers();
        $approvers = match ($stage->approvers()) {
            WorkflowApproval::ThemeApproval => $this->themeApprovers($progress),
            WorkflowApproval::ReceptionApproval => $this->receptionApprovers(),
        };
        foreach ($approvers as $approver) {
            $progress->addApprover($approver);
            $approver->setRoles(array_merge($approver->getRoles(), ['ROLE_APPROVER'])); // Give them the approver role
        }
        // todo there should probably be a flag that this is ready for approval
        // todo this needs to also fire off any associated events
    }

    public function disapprove(PersonWorkflowProgress $progress)
    {
        // todo stub
    }

    public function approve(PersonWorkflowProgress $progress)
    {
        // todo stub
    }

    /**
     * @param PersonWorkflowProgress $progress
     * @return Person[]
     */
    private function themeApprovers(PersonWorkflowProgress $progress): array
    {
        $theme = $progress->getPerson()->getThemeAffiliations()[0]->getTheme(
        ); // todo this is a little naive, but works for entries
        $admins = $this->personRepository()->findByRoleInTheme($theme, ThemeRole::ThemeAdmin);
        $managers = $this->personRepository()->findByRoleInTheme($theme, ThemeRole::LabManager);
        return array_merge($admins, $managers); // todo fall back to somebody if there are no theme approvers
    }

    /**
     * @return Person[]
     */
    private function receptionApprovers(): array
    {
        return $this->personRepository()->findByRole('ROLE_KEY_MANAGER');
    }

    #[SubscribedService]
    private function personRepository(): PersonRepository
    {
        return $this->container->get(__CLASS__ . '::' . __FUNCTION__);
    }

    #[SubscribedService]
    private function personEntryWorkflowProgressRepository(): PersonEntryWorkflowProgressRepository
    {
        return $this->container->get(__CLASS__ . '::' . __FUNCTION__);
    }
}