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
use App\Form\Workflow\PersonEntry\ApproveCertificatesFormType;
use App\Form\Workflow\PersonEntry\ApproveEntryFormType;
use App\Form\Workflow\PersonEntry\CertificateUploadType;
use App\Form\Workflow\PersonEntry\EntryFormType;
use App\Form\Workflow\PersonEntry\RejectCertificatesFormType;
use App\Form\Workflow\PersonEntry\RejectEntryFormType;
use App\Log\ActivityLogger;
use App\Repository\PersonRepository;
use DateTimeInterface;
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
    /**
     * This route provides a blank entry form for a new user
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param ActivityLogger $logger
     * @param WorkflowInterface $membershipStateMachine
     * @return Response
     */
    #[Route('/membership/entry-form', name: 'workflow_entry_entry_form')]
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
            $this->processEntryForm($person, $themeAffiliation->getStartedAt(), $em, $logger, $membershipStateMachine);

            $em->flush();

            // TODO redirect to some kind of workflow progress page? Or display workflow progress on view?
            return $this->redirectToRoute('person_view', ['slug' => $person->getSlug()]);
        }

        return $this->render('workflow/person_entry/entry_form.html.twig', [
            'person' => $person,
            'form' => $form->createView(),
        ]);
    }

    #[Route('membership/continue-entry-form/{slug}', name: 'app_workflow_membershipworkflow_continueentryform')]
    public function continueEntryForm(
        Person $person,
        Request $request,
        EntityManagerInterface $em,
        WorkflowInterface $membershipStateMachine,
        ActivityLogger $logger
    ): Response {
        if ($this->getUser() !== $person) {
            throw $this->createAccessDeniedException();
        }
        // Create any missing affiliations
        if ($person->getRoomAffiliations()->count() === 0) {
            $roomAffiliation = new RoomAffiliation();
            $person->addRoomAffiliation($roomAffiliation);
        }
        if ($person->getDepartmentAffiliations()->count() === 0) {
            $departmentAffiliation = new DepartmentAffiliation();
            $person->addDepartmentAffiliation($departmentAffiliation);
        }
        if ($person->getSupervisorAffiliations()->count() === 0) {
            $supervisorAffiliation = new SupervisorAffiliation();
            $person->addSupervisorAffiliation($supervisorAffiliation);
        }

        $form = $this->createForm(EntryFormType::class, $person)
            ->add('submit', SubmitType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // todo what do we do if the person has more than one theme affiliation? does it matter?
            $startDate = $person->getThemeAffiliations()[0]->getStartedAt();

            $this->processEntryForm($person, $startDate, $em, $logger, $membershipStateMachine);

            $em->flush();

            // TODO redirect to some kind of workflow progress page? Or display workflow progress on view?
            return $this->redirectToRoute('person_view', ['slug' => $person->getSlug()]);
        }

        return $this->render('workflow/person_entry/entry_form.html.twig', [
            'person' => $person,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/membership/approve-entry-form/{slug}', name: 'workflow_entry_approve_entry')]
    public function approveEntryForm(
        Person $person,
        Request $request,
        EntityManagerInterface $em,
        WorkflowInterface $membershipStateMachine,
        ActivityLogger $logger
    ): Response {
        // todo restrict this route to only assigned approvers
        $approvalForm = $this->createForm(ApproveEntryFormType::class)
            ->add('approve', SubmitType::class);
        $rejectionForm = $this->createForm(RejectEntryFormType::class, $person)
            ->add('return', SubmitType::class);

        $approvalForm->handleRequest($request);
        if ($approvalForm->isSubmitted() && $approvalForm->isValid()) {
            $membershipStateMachine->apply($person, 'approve_entry_form');
            $person->setMembershipNote(null);
            $logger->log($person, "Approved entry form");
            $em->flush();
            return $this->redirectToRoute('workflow_approvals');
        }

        $rejectionForm->handleRequest($request);
        if ($rejectionForm->isSubmitted() && $rejectionForm->isValid()) {
            $membershipStateMachine->apply($person, 'return_entry_form');
            $logger->log($person, sprintf("Returned entry form with reason \"%s\"", $person->getMembershipNote()));
            $em->flush();
            return $this->redirectToRoute('workflow_approvals');
        }

        return $this->render('workflow/person_entry/approve_entry_form.html.twig', [
            'person' => $person,
            'approvalForm' => $approvalForm->createView(),
            'rejectionForm' => $rejectionForm->createView(),
        ]);
    }

    #[Route('/membership/approvals', name: 'workflow_approvals')]
    #[IsGranted('ROLE_APPROVER')]
    public function approvalIndex(WorkflowInterface $membershipStateMachine, PersonRepository $repository): Response
    {
        $peopleToApprove = $repository->findAllNeedingApproval();
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

    #[Route('/membership/certificate-upload/{slug}', name: 'workflow_entry_upload_certs')]
    public function certificateUpload(
        Person $person,
        Request $request,
        EntityManagerInterface $em,
        WorkflowInterface $membershipStateMachine,
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
            $membershipStateMachine->apply($person, 'upload_certificates');

            $em->flush();

            return $this->redirectToRoute('person_view', ['slug' => $person->getSlug()]);
        }

        return $this->render('workflow/person_entry/upload_certs.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/membership/approve-certificates/{slug}', name: 'app_workflow_membershipworkflow_approvecerts')]
    public function approveCerts(
        Person $person,
        Request $request,
        EntityManagerInterface $em,
        WorkflowInterface $membershipStateMachine,
        ActivityLogger $logger
    ) {
        // todo restrict this route to only assigned approvers
        $approvalForm = $this->createForm(ApproveCertificatesFormType::class)
            ->add('approve', SubmitType::class);
        $rejectionForm = $this->createForm(RejectCertificatesFormType::class, $person)
            ->add('return', SubmitType::class);

        $approvalForm->handleRequest($request);
        if ($approvalForm->isSubmitted() && $approvalForm->isValid()) {
            $membershipStateMachine->apply($person, 'approve_certificates');
            $person->setMembershipNote(null);
            $logger->log($person, "Approved certificates");
            $em->flush();
            return $this->redirectToRoute('workflow_approvals');
        }

        $rejectionForm->handleRequest($request);
        if ($rejectionForm->isSubmitted() && $rejectionForm->isValid()) {
            $membershipStateMachine->apply($person, 'return_certificates');
            $logger->log($person, sprintf("Returned certificates with reason \"%s\"", $person->getMembershipNote()));
            $em->flush();
            return $this->redirectToRoute('workflow_approvals');
        }
        return $this->render('workflow/person_entry/approve_certs.html.twig', [
            'person' => $person,
            'approvalForm' => $approvalForm->createView(),
            'rejectionForm' => $rejectionForm->createView(),
        ]);
    }

    protected function processEntryForm(
        Person $person,
        DateTimeInterface $startDate,
        EntityManagerInterface $em,
        ActivityLogger $logger,
        WorkflowInterface $membershipStateMachine
    ): void {
        $roomAffiliation = $person->getRoomAffiliations()[0];
        $supervisorAffiliation = $person->getSupervisorAffiliations()[0];
        $departmentAffiliation = $person->getDepartmentAffiliations()[0];

        $roomAffiliation->setStartedAt($startDate);
        $supervisorAffiliation->setStartedAt($startDate);
        $departmentAffiliation->setStartedAt($startDate);
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
    }
}