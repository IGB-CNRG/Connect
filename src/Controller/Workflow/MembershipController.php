<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Controller\Workflow;

use App\Entity\Document;
use App\Entity\ExitForm;
use App\Entity\Person;
use App\Entity\RoomAffiliation;
use App\Entity\SupervisorAffiliation;
use App\Entity\ThemeAffiliation;
use App\Entity\UnitAffiliation;
use App\Enum\DocumentCategory;
use App\Form\Workflow\ApproveType;
use App\Form\Workflow\Membership\Certificate\CertificateUploadType;
use App\Form\Workflow\Membership\EntryForm\EntryFormType;
use App\Form\Workflow\Membership\ExitForm\ExitFormApprovalType;
use App\Form\Workflow\Membership\ExitForm\ExitFormType;
use App\Form\Workflow\RejectType;
use App\Log\ActivityLogger;
use App\Repository\PersonRepository;
use App\Service\CertificateHelper;
use App\Service\HistoricityManager;
use App\Workflow\Membership;
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

class MembershipController extends AbstractController
{
    /**
     * This route provides a blank entry form for a new user
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param ActivityLogger $logger
     * @param WorkflowInterface $membershipStateMachine
     * @return Response
     */
    #[Route('/membership/entry-form', name: 'membership_entryForm')]
    public function entryForm(
        Request $request,
        EntityManagerInterface $em,
        ActivityLogger $logger,
        WorkflowInterface $membershipStateMachine
    ): Response {
        $roomAffiliation = new RoomAffiliation();
        $unitAffiliation = new UnitAffiliation();
        $themeAffiliation = new ThemeAffiliation();
        $supervisorAffiliation = new SupervisorAffiliation();
        $person = (new Person())
            ->addRoomAffiliation($roomAffiliation)
            ->addUnitAffiliation($unitAffiliation)
            ->addThemeAffiliation($themeAffiliation)
            ->addSupervisorAffiliation($supervisorAffiliation);
        $form = $this->createForm(EntryFormType::class, $person)
            ->add('submit', SubmitType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->processEntryForm($person, $themeAffiliation->getStartedAt(), $em, $logger, $membershipStateMachine);

            $em->flush();

            return $this->redirectToRoute('person_view', ['slug' => $person->getSlug()]);
        }

        return $this->render('workflow/membership/entry_form.html.twig', [
            'person' => $person,
            'form' => $form->createView(),
        ]);
    }

    #[Route('membership/continue-entry-form/{slug}', name: 'membership_continueEntryForm')]
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
        if ($person->getUnitAffiliations()->count() === 0) {
            $unitAffiliation = new UnitAffiliation();
            $person->addUnitAffiliation($unitAffiliation);
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

            return $this->redirectToRoute('person_view', ['slug' => $person->getSlug()]);
        }

        return $this->render('workflow/membership/entry_form.html.twig', [
            'person' => $person,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/membership/approve-entry-form/{slug}', name: 'membership_approveEntryForm')]
    #[IsGranted('ROLE_APPROVER')]
    public function approveEntryForm(
        Person $person,
        Request $request,
        EntityManagerInterface $em,
        WorkflowInterface $membershipStateMachine,
        ActivityLogger $logger
    ): Response {
        // todo restrict this route to only assigned approvers
        $approvalForm = $this->createForm(ApproveType::class, null, [
            'approve_label' => 'I approve this IGB entry form',
        ])
            ->add('approve', SubmitType::class);
        $rejectionForm = $this->createForm(RejectType::class, $person, [
            'reject_label' => "Return the form with the following note",
        ])
            ->add('return', SubmitType::class);

        $approvalForm->handleRequest($request);
        if ($approvalForm->isSubmitted() && $approvalForm->isValid()) {
            $membershipStateMachine->apply($person, Membership::TRANS_APPROVE_ENTRY_FORM);
            $person->setMembershipNote(null);
            $logger->log($person, "Approved entry form");
            $em->flush();

            return $this->redirectToRoute('membership_approvals');
        }

        $rejectionForm->handleRequest($request);
        if ($rejectionForm->isSubmitted() && $rejectionForm->isValid()) {
            $membershipStateMachine->apply($person, Membership::TRANS_RETURN_ENTRY_FORM);
            $logger->log($person, sprintf("Returned entry form with reason \"%s\"", $person->getMembershipNote()));
            $em->flush();

            return $this->redirectToRoute('membership_approvals');
        }

        return $this->render('workflow/membership/approve_entry_form.html.twig', [
            'person' => $person,
            'approvalForm' => $approvalForm->createView(),
            'rejectionForm' => $rejectionForm->createView(),
        ]);
    }

    #[Route('/membership/approvals', name: 'membership_approvals')]
    #[IsGranted('ROLE_APPROVER')]
    public function approvalIndex(Membership $membership, PersonRepository $repository): Response
    {
        $peopleToApprove = $repository->findAllNeedingApproval();
        $myApprovals = array_filter($peopleToApprove, function (Person $person) use ($membership) {
            return $membership->canApprove($person);
        });

        return $this->render('workflow/approvals.html.twig', [
            'approvals' => $myApprovals,
        ]);
    }

    #[Route('/membership/certificate-upload', name: 'membership_certificateUpload')]
    public function certificateUpload(
        Request $request,
        CertificateHelper $certificateHelper,
        EntityManagerInterface $em,
        WorkflowInterface $membershipStateMachine,
        ActivityLogger $logger
    ): RedirectResponse|Response {
        // todo should we lock this down explicitly, or should we add an approvalstrategy for this step?
        if (array_keys($membershipStateMachine->getMarking($this->getUser())->getPlaces())[0]
            != Membership::PLACE_NEED_CERTIFICATES) {
            throw $this->createAccessDeniedException();
        }
        /** @var Person $person */
        $person = $this->getUser();
        if (!$membershipStateMachine->can($person, Membership::TRANS_UPLOAD_CERTIFICATES)) {
            throw $this->createAccessDeniedException();
        }
        $neededCertificates = $certificateHelper->requiredCertificates($person);
        foreach ($neededCertificates as $neededCertificate) {
            $certificateName = "$neededCertificate Training Certificate";
            if (!$person->getDocuments()->exists(
                fn($i, Document $document) => $document->getType() === DocumentCategory::Certificate
                                              && $document->getDisplayName() === $certificateName
            )) {
                // Look for an existing certificate, create if needed
                $certificate = (new Document())
                    ->setType(DocumentCategory::Certificate)
                    ->setDisplayName($certificateName)
                    ->setUploadedBy($this->getUser());
                $person->addDocument($certificate);
            }
        }

        $form = $this->createForm(CertificateUploadType::class, $person)
            ->add('submit', SubmitType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($person);

            $logger->log($person, 'Uploaded training certificates');
            $membershipStateMachine->apply($person, Membership::TRANS_UPLOAD_CERTIFICATES);

            $em->flush();

            return $this->redirectToRoute('person_view', ['slug' => $person->getSlug()]);
        }

        return $this->render('workflow/membership/upload_certs.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/membership/approve-certificates/{slug}', name: 'membership_approveCertificates')]
    #[IsGranted('ROLE_APPROVER')]
    public function approveCertificates(
        Person $person,
        Request $request,
        EntityManagerInterface $em,
        WorkflowInterface $membershipStateMachine,
        ActivityLogger $logger
    ): RedirectResponse|Response {
        // todo restrict this route to only assigned approvers
        $approvalForm = $this->createForm(ApproveType::class, null, [
            'approve_label' => 'I approve these training certificates',
        ])
            ->add('approve', SubmitType::class);
        $rejectionForm = $this->createForm(RejectType::class, $person, [
            'reject_label' => "Return the certificates with the following note",
        ])
            ->add('return', SubmitType::class);

        $approvalForm->handleRequest($request);
        if ($approvalForm->isSubmitted() && $approvalForm->isValid()) {
            $membershipStateMachine->apply($person, Membership::TRANS_APPROVE_CERTIFICATES);
            $person->setMembershipNote(null);
            $logger->log($person, "Approved certificates");
            $em->flush();

            return $this->redirectToRoute('membership_approvals');
        }

        $rejectionForm->handleRequest($request);
        if ($rejectionForm->isSubmitted() && $rejectionForm->isValid()) {
            $membershipStateMachine->apply($person, Membership::TRANS_RETURN_CERTIFICATES);
            $logger->log($person, sprintf("Returned certificates with reason \"%s\"", $person->getMembershipNote()));
            $em->flush();

            return $this->redirectToRoute('membership_approvals');
        }

        return $this->render('workflow/membership/approve_certs.html.twig', [
            'person' => $person,
            'approvalForm' => $approvalForm->createView(),
            'rejectionForm' => $rejectionForm->createView(),
        ]);
    }

    #[Route('/membership/exit-form/{slug}', name: 'membership_exitForm')]
    public function exitForm(
        Person $person,
        Request $request,
        EntityManagerInterface $entityManager,
        HistoricityManager $historicityManager,
        ActivityLogger $logger,
        WorkflowInterface $membershipStateMachine
    ): Response {
        if (!($person === $this->getUser()
              || $membershipStateMachine->can($person, Membership::TRANS_FORCE_EXIT_FORM))) {
            throw $this->createAccessDeniedException();
        }

        $exitForm = new ExitForm();
        $form = $this->createForm(ExitFormType::class, $exitForm)
            ->add('submit', SubmitType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($membershipStateMachine->can($person, Membership::TRANS_FORCE_EXIT_FORM)) {
                // Set exit reason and end date on all current theme, supervisor, and room affiliations
                $this->processExit($historicityManager, $person, $exitForm->getEndedAt(), $exitForm->getExitReason());
                $entityManager->persist($person);
                $membershipStateMachine->apply($person, Membership::TRANS_FORCE_EXIT_FORM);
            } else {
                // Submit exit form for approval
                $person->setExitForm($exitForm);
                $membershipStateMachine->apply($person, Membership::TRANS_SUBMIT_EXIT_FORM);
            }
            $logger->log(
                $person,
                "Submitted exit form (end date {$exitForm->getEndedAt()->format('n/j/Y')}, exit reason \"{$exitForm->getExitReason()}\")"
            );
            $entityManager->flush();

            return $this->redirectToRoute('person_view', ['slug' => $person->getSlug()]);
        }

        return $this->render('workflow/membership/exit_form.html.twig', [
            'person' => $person,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/membership/exit-form/{slug}/approval', name: 'membership_approveExitForm')]
    #[IsGranted('ROLE_APPROVER')]
    public function exitFormApproval(
        Person $person,
        Request $request,
        EntityManagerInterface $entityManager,
        WorkflowInterface $membershipStateMachine,
        HistoricityManager $historicityManager,
        ActivityLogger $logger
    ): Response {
        $form = $this->createForm(
            ExitFormApprovalType::class,
            ['endedAt' => $person->getExitForm()->getEndedAt()],
            ['approve_label' => 'I approve this exit form']
        )
            ->add('approve', SubmitType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $endedAt = $form->get('endedAt')->getData();
            $exitReason = $form->get('exitReason')->getData();
            $this->processExit(
                $historicityManager,
                $person,
                $endedAt,
                $exitReason
            );
            $entityManager->persist($person);
            $membershipStateMachine->apply($person, Membership::TRANS_DEACTIVATE);
            $logger->log(
                $person,
                "Approved exit form (end date {$endedAt->format('n/j/Y')}, exit reason \"{$exitReason}\")"
            );
            $entityManager->flush();

            return $this->redirectToRoute('membership_approvals');
        }

        return $this->render('workflow/membership/approve_exit_form.html.twig', [
            'person' => $person,
            'form' => $form,
        ]);
    }

    /**
     * @param HistoricityManager $historicityManager
     * @param Person $person
     * @param DateTimeInterface $endedAt
     * @param string $exitReason
     * @return void
     */
    protected function processExit(
        HistoricityManager $historicityManager,
        Person $person,
        DateTimeInterface $endedAt,
        string $exitReason
    ): void {
        $historicityManager->endAffiliations(
            array_merge(
                $person->getSupervisorAffiliations()->toArray(),
                $person->getRoomAffiliations()->toArray(),
                $person->getThemeAffiliations()->toArray(),
                $person->getUnitAffiliations()->toArray(),
                $person->getSuperviseeAffiliations()->toArray()
            ),
            $endedAt,
            $exitReason
        );
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
        $unitAffiliation = $person->getUnitAffiliations()[0];

        $roomAffiliation->setStartedAt($startDate);
        $supervisorAffiliation->setStartedAt($startDate);
        $unitAffiliation->setStartedAt($startDate);
        if (!$roomAffiliation->getRoom()) {
            $person->removeRoomAffiliation($roomAffiliation);
        }
        if (!$unitAffiliation->getUnit() && !$unitAffiliation->getOtherUnit()) {
            $person->removeUnitAffiliation($unitAffiliation);
        }
        if (!$supervisorAffiliation->getSupervisor()) {
            $person->removeSupervisorAffiliation($supervisorAffiliation);
        }
        $person->setUsername($person->getNetid());
        $em->persist($person);

        $logger->log($person, 'Submitted entry form');
        $membershipStateMachine->apply($person, Membership::TRANS_SUBMIT_ENTRY_FORM);
    }
}