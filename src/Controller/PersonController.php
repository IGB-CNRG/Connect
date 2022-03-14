<?php

namespace App\Controller;

use App\Entity\Document;
use App\Entity\Note;
use App\Entity\Person;
use App\Entity\SupervisorAffiliation;
use App\Entity\ThemeAffiliation;
use App\Form\DocumentMetadataType;
use App\Form\DocumentType;
use App\Form\EndThemeAffiliationType;
use App\Form\NoteType;
use App\Form\PersonType;
use App\Form\SuperviseeType;
use App\Form\SupervisorType;
use App\Form\ThemeAffiliationType;
use App\Repository\PersonRepository;
use App\Service\ActivityLogger;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PersonController extends AbstractController
{
    #[Route('/person', name: 'person')]
    public function index(PersonRepository $personRepository): Response
    {
        $people = $personRepository->findAllForIndex();
        return $this->render('person/index.html.twig', [
            'people' => $people,
        ]);
    }

    #[Route('/person/{slug}', name: 'person_view')]
    public function view(Person $person): Response
    {
        return $this->render('person/view.html.twig', [
            'person' => $person,
        ]);
    }

    #[Route('/person/{slug}/edit', name: 'person_edit')]
    public function edit(
        Person $person,
        Request $request,
        EntityManagerInterface $em,
        ActivityLogger $logger
    ): Response {
        $form = $this->createForm(PersonType::class, $person);
        $form->add('save', SubmitType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($person);
            $logger->logPersonEdit($person);
            $em->flush();

            return $this->redirectToRoute('person_view', ['slug' => $person->getSlug()]);
        }
        return $this->render('person/edit.html.twig', [
            'person' => $person,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/person/new', name: 'person_add', priority: 1)]
    public function new(Request $request, EntityManagerInterface $em, ActivityLogger $logger): Response
    {
        $person = new Person();
        $form = $this->createForm(PersonType::class, $person);
        $form->add('save', SubmitType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($person);
            $logger->logPersonActivity($person, 'Created record');
            $em->flush();

            return $this->redirectToRoute('person_view', ['slug' => $person->getSlug()]);
        }
        return $this->render('person/new.html.twig', [
            'person' => $person,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/person/{slug}/add-theme-affiliation', name: 'person_add_theme_affiliation')]
    public function newThemeAffiliation(
        Person $person,
        Request $request,
        EntityManagerInterface $em,
        ActivityLogger $logger
    ): Response {
        $themeAffiliation = (new ThemeAffiliation())
            ->setPerson($person);
        $form = $this->createForm(ThemeAffiliationType::class, $themeAffiliation, ['person' => $person]);
        $form->add('Add', SubmitType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($themeAffiliation);
            $logger->logNewThemeAffiliation($themeAffiliation);
            foreach ($form->get('endPreviousAffiliations')->getData() as $endingAffiliation) {
                /** @var ThemeAffiliation $endingAffiliation */
                $endingAffiliation->setEndedAt($themeAffiliation->getStartedAt());
                $em->persist($endingAffiliation);
                $logger->logEndThemeAffiliation($endingAffiliation);
            }

            $em->flush();

            return $this->redirectToRoute('person_view', ['slug' => $person->getSlug()]);
        }

        return $this->render('person/themeAffiliation/add.html.twig', [
            'person' => $person,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/themeAffiliation/{id}/end', name: 'person_end_theme_affiliation')]
    public function endThemeAffiliation(
        ThemeAffiliation $themeAffiliation,
        Request $request,
        EntityManagerInterface $em,
        ActivityLogger $logger
    ): Response {
        $form = $this->createForm(EndThemeAffiliationType::class, $themeAffiliation);
        $form->add('save', SubmitType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($themeAffiliation);
            $logger->logEndThemeAffiliation($themeAffiliation);
            $em->flush();

            return $this->redirectToRoute('person_view', ['slug' => $themeAffiliation->getPerson()->getSlug()]);
        }

        return $this->render('person/themeAffiliation/end.html.twig', [
            'person' => $themeAffiliation->getPerson(),
            'themeAffiliation' => $themeAffiliation,
            'form' => $form->createView(),
        ]);
    }

    /** @noinspection PhpParamsInspection */
    #[Route('/person/{slug}/upload-document', name: 'person_upload_document')]
    public function uploadDocument(Person $person, Request $request, EntityManagerInterface $em, ActivityLogger $logger)
    {
        $document = (new Document())
            ->setPerson($person)
            ->setUploadedBy($this->getUser());
        $form = $this->createForm(DocumentType::class, $document);
        $form->add('upload', SubmitType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($document);
            $logger->logPersonActivity($person, sprintf("Uploaded document '%s'", $document));
            $em->flush();

            return $this->redirectToRoute('person_view', ['slug' => $person->getSlug()]);
        }

        return $this->render('person/document/add.html.twig', [
            'person' => $person,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/document/{id}/edit', name: 'person_edit_document')]
    public function editDocument(
        Document $document,
        Request $request,
        EntityManagerInterface $em,
        ActivityLogger $logger
    ): Response {
        $form = $this->createForm(DocumentMetadataType::class, $document);
        $form->add('save', SubmitType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($document);
            $logger->logPersonActivity($document->getPerson(), sprintf("Edited document '%s'", $document));
            $em->flush();

            return $this->redirectToRoute('person_view', ['slug' => $document->getPerson()->getSlug()]);
        }

        return $this->render('person/document/add.html.twig', [
            'person' => $document->getPerson(),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/document/{id}/delete', name: 'person_delete_document')]
    public function deleteDocument(
        Document $document,
        Request $request,
        EntityManagerInterface $em,
        ActivityLogger $logger
    ): Response {
        $person = $document->getPerson();

        if ($request->isMethod(Request::METHOD_POST)) {
            $em->remove($document);
            $logger->logPersonActivity($person, sprintf("Removed document '%s'", $document));
            $em->flush();

            return $this->redirectToRoute('person_view', ['slug' => $person->getSlug()]);
        }

        return $this->render('person/document/delete.html.twig', [
            'person' => $person,
            'document' => $document,
        ]);
    }

    #[Route('/person/{slug}/add-note', name: 'person_add_note')]
    #[IsGranted('NOTE_ADD', subject: 'person')]
    public function addNote(
        Person $person,
        Request $request,
        EntityManagerInterface $em,
        ActivityLogger $logger
    ): Response {
        /** @noinspection PhpParamsInspection */
        $note = (new Note())
            ->setPerson($person)
            ->setCreatedBy($this->getUser());
        $form = $this->createForm(NoteType::class, $note);
        $form->add('save', SubmitType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($note);
            $logger->logPersonActivity($person, 'Added note'); // todo a little more detail?
            $em->flush();

            return $this->redirectToRoute('person_view', ['slug' => $person->getSlug()]);
        }

        return $this->render('person/note/edit.html.twig', [
            'person' => $person,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/note/{id}/edit', name: 'person_edit_note')]
    #[IsGranted('NOTE_EDIT', subject: 'note')]
    public function editNote(Note $note, Request $request, EntityManagerInterface $em, ActivityLogger $logger): Response
    {
        $person = $note->getPerson();
        $form = $this->createForm(NoteType::class, $note);
        $form->add('save', SubmitType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($note);
            $logger->logPersonActivity($person, 'Added note'); // todo a little more detail?
            $em->flush();

            return $this->redirectToRoute('person_view', ['slug' => $person->getSlug()]);
        }

        return $this->render('person/note/edit.html.twig', [
            'person' => $person,
            'note' => $note,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/note/{id}/delete', name: 'person_delete_note')]
    #[IsGranted('NOTE_EDIT', subject: 'note')]
    public function deleteNote(
        Note $note,
        Request $request,
        EntityManagerInterface $em,
        ActivityLogger $logger
    ): Response {
        $person = $note->getPerson();
        if ($request->isMethod(Request::METHOD_POST)) {
            $em->remove($note);
            $logger->logPersonActivity(
                $person,
                sprintf('Removed note from %s', $note->getCreatedAt()->format('n/j/Y'))
            );
            $em->flush();

            return $this->redirectToRoute('person_view', ['slug' => $person->getSlug()]);
        }

        return $this->render('person/note/delete.html.twig', [
            'person' => $person,
            'note' => $note,
        ]);
    }

    #[Route('/person/{slug}/add-supervisor', name: 'person_add_supervisor')]
    public function addSupervisor(Person $person, Request $request, EntityManagerInterface $em, ActivityLogger $logger)
    {
        $supervisorAffiliation = (new SupervisorAffiliation())
            ->setSupervisee($person);
        $form = $this->createForm(SupervisorType::class, $supervisorAffiliation, ['person' => $person]);
        $form->add('save', SubmitType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($supervisorAffiliation);
            $logger->logNewSupervisorAffiliation($supervisorAffiliation);
            $em->flush();

            return $this->redirectToRoute('person_view', ['slug' => $person->getSlug()]);
        }

        return $this->render('person/supervisor/add.html.twig', [
            'person' => $person,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/person/{slug}/add-supervisee', name: 'person_add_supervisee')]
    public function addSupervisee(Person $person, Request $request, EntityManagerInterface $em, ActivityLogger $logger)
    {
        $supervisorAffiliation = (new SupervisorAffiliation())
            ->setSupervisor($person);
        $form = $this->createForm(SuperviseeType::class, $supervisorAffiliation, ['person' => $person]);
        $form->add('save', SubmitType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($supervisorAffiliation);
            $logger->logNewSupervisorAffiliation($supervisorAffiliation);
            $em->flush();

            return $this->redirectToRoute('person_view', ['slug' => $person->getSlug()]);
        }

        return $this->render('person/supervisee/add.html.twig', [
            'person' => $person,
            'form' => $form->createView(),
        ]);
    }
}
