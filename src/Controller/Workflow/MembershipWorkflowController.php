<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Controller\Workflow;

use App\Entity\DepartmentAffiliation;
use App\Entity\Document;
use App\Entity\Person;
use App\Entity\RoomAffiliation;
use App\Entity\SupervisorAffiliation;
use App\Entity\ThemeAffiliation;
use App\Enum\DocumentCategory;
use App\Form\Workflow\PersonEntry\ApproveEntryFormType;
use App\Form\Workflow\PersonEntry\CertificateUploadType;
use App\Form\Workflow\PersonEntry\EntryFormType;
use App\Log\ActivityLogger;
use App\Repository\PersonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Workflow\WorkflowInterface;

class MembershipWorkflowController extends AbstractController
{
    #[Route('/workflow/entry/entry_form', name: 'workflow_entry_entry_form')]
    public function entryForm(
        Request $request,
        EntityManagerInterface $em,
        ActivityLogger $logger,
        WorkflowInterface $membershipStateMachine
    ): Response {
        // todo so far this form does not support saving and continuing. do we want to?
        // todo this form only represents the new member filling out a form for themselves while logged in as someone else

        $roomAffiliation = new RoomAffiliation();
        $departmentAffiliation = new DepartmentAffiliation();
        $themeAffiliation = new ThemeAffiliation();
        $supervisorAffiliation = new SupervisorAffiliation();
        $person = (new Person())
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

            $logger->log($person, 'Submitted entry form');
            $membershipStateMachine->apply($person, 'submit_entry_form');

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
        WorkflowInterface $membershipWorkflow,
        ActivityLogger $logger
    ): Response {
        // todo restrict this route to only assigned approvers
        $form = $this->createForm(ApproveEntryFormType::class)
            ->add('approve', SubmitType::class)
            ->add('return', SubmitType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // todo we need different validation groups based on whether the approver hit approve or reject
            if ($form->get('approve')->isClicked()) {
                $membershipWorkflow->apply($person, 'approve_entry_form');
                $logger->log($person, "Approved entry form");
            } else {
                $membershipWorkflow->apply($person, 'return_entry_form');
                $logger->log($person, "Returned entry form");
            }
            $em->flush();
            return $this->redirectToRoute('default'); // todo redirect to approval index, when implemented
        }

        return $this->render('workflow/person_entry/approve_entry_form.html.twig', [
            'person' => $person,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/workflow/approvals', name: 'workflow_approvals')]
    #[IsGranted('ROLE_APPROVER')]
    public function approvalIndex(WorkflowInterface $membershipStateMachine, PersonRepository $repository): Response
    {
        $peopleToApprove = $repository->findAllNeedingApproval();
        // TODO we need to test that the approval guard is working correctly
        $myApprovals = array_filter($peopleToApprove, function (Person $person) use ($membershipStateMachine) {
            // todo can we not hard code these transitions?
            return $membershipStateMachine->can($person, 'approve_entry_form')
                   || $membershipStateMachine->can(
                    $person,
                    'approve_certificates'
                );
        });

        return $this->render('workflow/approvals.html.twig', [
            'approvals' => $myApprovals,
        ]);
    }

    #[Route('/workflow/entry/certificate_upload/{slug}', name: 'workflow_entry_upload_certs')]
    public function certificateUpload(
        Person $person,
        Request $request,
        EntityManagerInterface $em,
        WorkflowInterface $membershipWorkflow,
        ActivityLogger $logger
    ): RedirectResponse|Response {
        $drsCert = (new Document())
            ->setType(DocumentCategory::Certificate)
            ->setDisplayName("DRS Training Certificate")
            ->setUploadedBy($this->getUser());
        $igbCert = (new Document())
            ->setType(DocumentCategory::Certificate)
            ->setDisplayName("IGB Training Certificate")
            ->setUploadedBy($this->getUser());

        $form = $this->createForm(CertificateUploadType::class, ['drs' => $drsCert, 'igb' => $igbCert])
            ->add('submit', SubmitType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $person->addDocument($drsCert)->addDocument($igbCert);
            $em->persist($drsCert);
            $em->persist($igbCert);

            $logger->log($person, 'Uploaded training certificates');
            $membershipWorkflow->apply($person, 'upload_certificates');

            $em->flush();

            return $this->redirectToRoute('person_view', ['slug' => $person->getSlug()]);
        }

        return $this->render('workflow/person_entry/upload_certs.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/workflow/entry/approve_certificates/{slug}', name: 'app_workflow_membershipworkflow_approvecerts')]
    public function approveCerts()
    {

    }
}