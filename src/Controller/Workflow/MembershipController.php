<?php
/*
 * Copyright (c) 2025 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Controller\Workflow;

use App\Entity\Document;
use App\Entity\ExitForm;
use App\Entity\Person;
use App\Entity\RoomAffiliation;
use App\Enum\DocumentCategory;
use App\Form\Workflow\ApproveType;
use App\Form\Workflow\Membership\Certificate\CertificateUploadType;
use App\Form\Workflow\Membership\EntryForm\EntryFormType;
use App\Form\Workflow\Membership\ExitForm\ExitFormApprovalType;
use App\Form\Workflow\Membership\ExitForm\ExitFormType;
use App\Form\Workflow\Membership\SendEntryFormType;
use App\Form\Workflow\RejectType;
use App\Log\ActivityLogger;
use App\Repository\PersonRepository;
use App\Service\CertificateHelper;
use App\Service\HistoricityManager;
use App\Service\ThemeAffiliationFactory;
use App\Settings\SettingManager;
use App\Workflow\Membership;
use Doctrine\ORM\EntityManagerInterface;
use Html2Text\Html2Text;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Workflow\WorkflowInterface;

class MembershipController extends AbstractController
{
    /**
     * This route provides a blank entry form for a new user
     * @param Person|null $person
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param WorkflowInterface $membershipStateMachine
     * @param ThemeAffiliationFactory $factory
     * @param Membership $membership
     * @param HistoricityManager $historicityManager
     * @return Response
     */
    #[Route('/membership/entry-form', name: 'membership_entryForm', defaults: ['person' => null])]
    #[Route('membership/entry-form/{slug}', name: 'membership_continueEntryForm')]
    #[Route('membership/reentry-form/{slug}', name: 'membership_reentryForm')] // todo does this even need to be a separate route from continue?
    public function entryForm(
        ?Person $person,
        Request $request,
        EntityManagerInterface $em,
        WorkflowInterface $membershipStateMachine,
        ThemeAffiliationFactory $factory,
        Membership $membership,
        HistoricityManager $historicityManager
    ): Response {
        if ($person === null && $request->get('_route') !== "membership_entryForm") {
            throw $this->createNotFoundException();
        }
        if ($person === null) {
            $person = new Person();
        }

        if ($request->get('_route') === "membership_reentryForm"
            && !$membershipStateMachine->can($person, Membership::TRANS_REACTIVATE)) {
            throw $this->createAccessDeniedException();
        }
        if (($request->get('_route') === "membership_entryForm"
                || $request->get('_route') === "membership_continueEntryForm")
            && !($membershipStateMachine->can($person, Membership::TRANS_FORCE_ENTRY_FORM)
                || $membershipStateMachine->can($person, Membership::TRANS_SUBMIT_ENTRY_FORM))) {
            throw $this->createAccessDeniedException();
        }

        // add new empty affiliations if there are no current affiliations (there shouldn't be!)
        if ($historicityManager->getCurrentAndFutureEntities($person->getRoomAffiliations())->count() === 0) {
            $person->addRoomAffiliation((new RoomAffiliation()));
        }
        if ($historicityManager->getCurrentAndFutureEntities($person->getThemeAffiliations())->count() === 0) {
            $person->addThemeAffiliation($factory->new());
        }

        // todo the allow_silent logic might need to change when we enable the workflow
        $form = $this->createForm(EntryFormType::class, $person, [
            'allow_silent' => $membershipStateMachine->can($person, Membership::TRANS_FORCE_ENTRY_FORM),
            'show_position_when_joined' => $this->isGranted('ROLE_ADMIN'),
            'allow_skip_uin' => $this->isGranted('ROLE_ADMIN'),
            'allow_skip_netid' => $this->isGranted('PERSON_ADD'),
            'use_captcha' => !$this->isGranted('IS_AUTHENTICATED_FULLY'),
        ])
            ->add('submit', SubmitType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $silent = $form->has('isSilent') && $form->get('isSilent')->getData();
            $membership->processEntry($person, $silent);

            $em->flush();

            // Show a success message if we're anonymous, otherwise show the newly-added person
            if($this->getUser()) {
                return $this->redirectToRoute('person_view', ['slug' => $person->getSlug()]);
            } else {
                return $this->render('workflow/membership/entry_form_success.html.twig');
            }
        }
        return $this->render('workflow/membership/entry_form.html.twig', [
            'person' => $person,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/membership/entry-form/{slug}/approve', name: 'membership_approveEntryForm')]
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
            // Skip the certificates if we can
            if ($membershipStateMachine->can($person, Membership::TRANS_ACTIVATE_WITHOUT_CERTIFICATES)) {
                $membershipStateMachine->apply($person, Membership::TRANS_ACTIVATE_WITHOUT_CERTIFICATES);
            } else {
                $membershipStateMachine->apply($person, Membership::TRANS_APPROVE_ENTRY_FORM);
            }
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

    #[Route('/membership/certificates', name: 'membership_certificateUpload')]
    public function certificateUpload(
        Request $request,
        CertificateHelper $certificateHelper,
        EntityManagerInterface $em,
        WorkflowInterface $membershipStateMachine,
        Membership $membership,
        ActivityLogger $logger
    ): RedirectResponse|Response {
        /** @var Person $person */
        $person = $this->getUser();
        // todo should we lock this down explicitly, or should we add an approvalstrategy for this step?
        if ($membership->getPlace($person) != Membership::PLACE_NEED_CERTIFICATES) {
            throw $this->createAccessDeniedException();
        }

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
                    ->setUploadedBy($person);
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

    #[Route('/membership/certificates/{slug}/approve', name: 'membership_approveCertificates')]
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
        Membership $membership,
        ActivityLogger $logger,
        WorkflowInterface $membershipStateMachine
    ): Response {
        if (!($person === $this->getUser()
            || $membershipStateMachine->can($person, Membership::TRANS_FORCE_EXIT_FORM))) {
            throw $this->createAccessDeniedException();
        }

        $exitForm = new ExitForm();
        $form = $this->createForm(ExitFormType::class, $exitForm, ['force' => $person !== $this->getUser()])
            ->add('submit', SubmitType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($membershipStateMachine->can($person, Membership::TRANS_FORCE_EXIT_FORM)) {
                // Set exit reason and end date on all current theme, supervisor, and room affiliations
                $membership->processExit(
                    $person,
                    $exitForm->getEndedAt(),
                    $exitForm->getExitReason(),
                    $exitForm->getForwardingEmail()
                );
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
        Membership $membership,
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
            $membership->processExit(
                $person,
                $endedAt,
                $exitReason,
                $person->getExitForm()->getForwardingEmail()
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

    #[Route('/membership/send-entry-form', name: 'membership_sendEntryForm')]
    public function sendEntryForm(
        Request $request,
        SettingManager $settingManager,
        MailerInterface $mailer
    ) {
        $success = false;
        $toAddress = null;
        $form = $this->createForm(SendEntryFormType::class)
            ->add('send', SubmitType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // send an email to the address given
            $toAddress = $form->get('email')->getData();
            $subject = 'IGB Entry Form Request';
            $htmlMessage = $settingManager->get('entry_invitation_template');

            $html2text = new Html2Text($htmlMessage);
            $textMessage = $html2text->getText();

            $email = (new TemplatedEmail())
                ->from($settingManager->get('notification_from'))
                ->to($toAddress)
                ->subject($subject)
                ->htmlTemplate('workflow/membership/entry_invitation.html.twig')
                ->textTemplate('workflow/membership/entry_invitation.txt.twig')
                ->context([
                    'subject' => $subject,
                    'message' => $htmlMessage,
                    'plainTextMessage' => $textMessage,
                ]);

            $mailer->send($email);

            $success = true;
        }

        return $this->render('workflow/membership/send_entry_form.html.twig', [
            'form' => $form->createView(),
            'invitationSent' => $success,
            'toAddress' => $toAddress,
        ]);
    }
}