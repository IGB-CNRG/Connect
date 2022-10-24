<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Controller\Workflow;

use App\Entity\DepartmentAffiliation;
use App\Entity\Person;
use App\Entity\RoomAffiliation;
use App\Entity\SupervisorAffiliation;
use App\Entity\ThemeAffiliation;
use App\Entity\Workflow\PersonEntryWorkflowProgress;
use App\Enum\Workflow\PersonEntryStage;
use App\Form\Workflow\PersonEntry\ApproveEntryFormType;
use App\Form\Workflow\PersonEntry\EntryFormType;
use App\Service\ActivityLogger;
use App\Service\WorkflowManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PersonEntryController extends AbstractController
{
    #[Route('/workflow/entry/entry_form', name: 'workflow_entry_entry_form')]
    public function entryForm(
        Request $request,
        EntityManagerInterface $em,
        ActivityLogger $logger,
        WorkflowManager $workflowManager
    ): Response {
        // todo so far this form does not support saving and continuing. do we want to?
        // todo this form only represents the new member filling out a form for themselves while logged in as someone else
        $roomAffiliation = new RoomAffiliation();
        $departmentAffiliation = new DepartmentAffiliation();
        $themeAffiliation = new ThemeAffiliation();
        $supervisorAffiliation = new SupervisorAffiliation();
        $workflowProgress = (new PersonEntryWorkflowProgress())->setStage(PersonEntryStage::SubmitEntryForm);
        $person = (new Person())
            ->setPersonEntryWorkflowProgress($workflowProgress)
            ->addRoomAffiliation($roomAffiliation)
            ->addDepartmentAffiliation($departmentAffiliation)
            ->addThemeAffiliation($themeAffiliation)
            ->addSupervisorAffiliation($supervisorAffiliation);
        $form = $this->createForm(EntryFormType::class, $person)
            ->add('submit', SubmitType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $roomAffiliation->setStartedAt($themeAffiliation->getStartedAt());
            $supervisorAffiliation->setStartedAt($themeAffiliation->getStartedAt());
            $departmentAffiliation->setStartedAt($themeAffiliation->getStartedAt());
            if (!$roomAffiliation->getRoom()) {
                $person->removeRoomAffiliation($roomAffiliation);
            }
            if (!$departmentAffiliation->getDepartment() && !$departmentAffiliation->getOtherDepartment()) {
                $person->removeDepartmentAffiliation($departmentAffiliation);
            }
            $person->setUsername($person->getNetid());
            $em->persist($person);

            $logger->logEntryFormSubmitted($person);
            // todo should there be a check that this person is at the correct stage?
            $workflowManager->submitForApproval($workflowProgress);

            $em->flush();

            // TODO redirect to some kind of workflow progress page? Or display workflow progress on view?
            return $this->redirectToRoute('person_view', ['slug' => $person->getSlug()]);
        }

        return $this->render('workflow/person_entry/entry_form.html.twig', [
            'person' => $person,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/workflow/entry/approve_entry_form/{slug}', name: 'workflow_entry_approve_entry')]
    public function approveEntryForm(
        Person $person,
        Request $request,
        EntityManagerInterface $em,
        WorkflowManager $workflowManager
    ): Response {
        // todo restrict this route to only assigned approvers
        $form = $this->createForm(ApproveEntryFormType::class)
            ->add('approve', SubmitType::class)
            ->add('reject', SubmitType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // todo we need different validation groups based on whether the approver hit approve or reject
            if ($form->get('approve')->isClicked()) {
                $workflowManager->approve($person->getPersonEntryWorkflowProgress());
            } else {
                $workflowManager->disapprove($person->getPersonEntryWorkflowProgress());
            }
            $em->flush();
            return $this->redirectToRoute('default'); // todo redirect to approval index, when implemented
        }

        return $this->render('workflow/person_entry/approve_entry_form.html.twig', [
            'person' => $person,
            'form' => $form->createView(),
        ]);
    }
}