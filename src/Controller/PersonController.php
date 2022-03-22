<?php

namespace App\Controller;

use App\Entity\Document;
use App\Entity\Note;
use App\Entity\Person;
use App\Entity\ThemeAffiliation;
use App\Form\DocumentMetadataType;
use App\Form\DocumentType;
use App\Form\EndThemeAffiliationType;
use App\Form\KeysType;
use App\Form\NoteType;
use App\Form\Person\PersonType;
use App\Form\ThemeAffiliationType;
use App\Repository\MemberCategoryRepository;
use App\Repository\PersonRepository;
use App\Repository\ThemeRepository;
use App\Service\ActivityLogger;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PersonController extends AbstractController
{
    #[Route('/person', name: 'person')]
    public function index(
        PersonRepository $personRepository,
        ThemeRepository $themeRepository,
        MemberCategoryRepository $categoryRepository
    ): Response {
        $people = $personRepository->findAllForIndex();
        $themes = $themeRepository->findAll(); // Todo group by current/old?
        $memberCategories = $categoryRepository->findAll(); // todo sort
        return $this->render('person/index.html.twig', [
            'people' => $people,
            'themes' => $themes,
            'memberCategories' => $memberCategories,
        ]);
    }

    #[Route('/person/{slug}', name: 'person_view')]
    #[IsGranted('PERSON_VIEW', subject: 'person')]
    public function view(Person $person): Response
    {
        return $this->render('person/view.html.twig', [
            'person' => $person,
        ]);
    }

    #[Route('/person/{slug}/edit', name: 'person_edit')]
    #[IsGranted('PERSON_EDIT', subject: 'person')]
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
    #[IsGranted('PERSON_ADD')]
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
    #[IsGranted('PERSON_EDIT', 'person')]
    public function newThemeAffiliation(
        Person $person,
        Request $request,
        EntityManagerInterface $em,
        ActivityLogger $logger
    ): Response {
        $themeAffiliation = (new ThemeAffiliation())
            ->setPerson($person)
        ;
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

    #[Route('/person/{slug}/themeAffiliation/{id}/end', name: 'person_end_theme_affiliation')]
    #[ParamConverter('person', options: ['mapping' => ['slug' => 'slug']])]
    #[IsGranted('PERSON_EDIT', 'person')]
    public function endThemeAffiliation(
        Person $person,
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

            return $this->redirectToRoute('person_view', ['slug' => $person->getSlug()]);
        }

        return $this->render('person/themeAffiliation/end.html.twig', [
            'person' => $person,
            'themeAffiliation' => $themeAffiliation,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/person/{slug}/upload-document', name: 'person_upload_document')]
    #[IsGranted('PERSON_EDIT', subject: 'person')]
    public function uploadDocument(
        Person $person,
        Request $request,
        EntityManagerInterface $em,
        ActivityLogger $logger
    ): Response {
        /** @noinspection PhpParamsInspection */
        $document = (new Document())
            ->setPerson($person)
            ->setUploadedBy($this->getUser())
        ;
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

    #[Route('/person/{slug}/document/{id}/edit', name: 'person_edit_document')]
    #[ParamConverter('person', options: ['mapping' => ['slug' => 'slug']])]
    #[IsGranted('PERSON_EDIT', subject: 'person')]
    public function editDocument(
        Person $person,
        Document $document,
        Request $request,
        EntityManagerInterface $em,
        ActivityLogger $logger
    ): Response {
        // todo break if the document does not belong to the person?
        $form = $this->createForm(DocumentMetadataType::class, $document);
        $form->add('save', SubmitType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($document);
            $logger->logPersonActivity($person, sprintf("Edited document '%s'", $document));
            $em->flush();

            return $this->redirectToRoute('person_view', ['slug' => $person->getSlug()]);
        }

        return $this->render('person/document/add.html.twig', [
            'person' => $person,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/person/{slug}/document/{id}/delete', name: 'person_delete_document')]
    #[ParamConverter('person', options: ['mapping' => ['slug' => 'slug']])]
    public function deleteDocument(
        Person $person,
        Document $document,
        Request $request,
        EntityManagerInterface $em,
        ActivityLogger $logger
    ): Response {
        $this->denyAccessUnlessGranted('PERSON_EDIT', $person);

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

    #[Route('/person/{slug}/note/{id}/edit', name: 'person_edit_note')]
    #[Route('/person/{slug}/add-note', name: 'person_add_note')]
    #[ParamConverter('person', options: ['mapping' => ['slug' => 'slug']])]
    public function editNote(
        Person $person,
        ?Note $note,
        Request $request,
        EntityManagerInterface $em,
        ActivityLogger $logger
    ): Response {
        if ($note === null) {
            $this->denyAccessUnlessGranted('NOTE_ADD');
            /** @noinspection PhpParamsInspection */
            $note = (new Note())
                ->setPerson($person)
                ->setCreatedBy($this->getUser())
            ;
        } else {
            $this->denyAccessUnlessGranted('NOTE_EDIT', $note);
        }
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

    #[Route('/person/{slug}/note/{id}/delete', name: 'person_delete_note')]
    #[ParamConverter('person', options: ['mapping' => ['slug' => 'slug']])]
    #[IsGranted('NOTE_EDIT', subject: 'note')]
    public function deleteNote(
        Person $person,
        Note $note,
        Request $request,
        EntityManagerInterface $em,
        ActivityLogger $logger
    ): Response {
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

    #[Route('/person/{id}/edit-keys', name: 'person_edit_keys')]
    #[IsGranted('ROLE_KEY_MANAGER')]
    public function editKeys(
        Person $person,
        Request $request,
        EntityManagerInterface $em,
        ActivityLogger $logger
    ): Response {
        $form = $this->createForm(KeysType::class, $person);
        $form->add('save', SubmitType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($person);
            $logger->logPersonEdit($person);
            $em->flush();

            return $this->redirectToRoute('person_view', ['slug' => $person->getSlug()]);
        }

        return $this->render('person/keys/edit.html.twig', [
            'person' => $person,
            'form' => $form->createView(),
        ]);
    }
}
