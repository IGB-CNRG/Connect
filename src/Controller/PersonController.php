<?php
/*
 * Copyright (c) 2024 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Controller;

use App\Entity\Person;
use App\Entity\RoomAffiliation;
use App\Entity\ThemeAffiliation;
use App\Form\EndAffiliationType;
use App\Form\KeysType;
use App\Form\Person\FilterType;
use App\Form\Person\PersonType;
use App\Form\Person\RoomAffiliationType;
use App\Form\Person\ThemeAffiliationType;
use App\Form\Workflow\Membership\ExitForm\ExitReasonType;
use App\Log\ActivityLogger;
use App\Repository\PersonRepository;
use App\Service\HistoricityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PersonController extends AbstractController
{
    #[Route('/members', name: 'person_currentmembers')]
    public function currentMembers(PersonRepository $personRepository): Response
    {
        $people = $personRepository->findCurrentForMembersOnlyIndex();

        return $this->index($people);
    }

    #[Route('/members/all', name: 'person_allmembers')]
    public function allMembers(PersonRepository $personRepository): Response
    {
        $people = $personRepository->findAllForMembersOnlyIndex();

        return $this->index($people);
    }

    #[Route('/people', name: 'person_currentpeople')]
    public function currentPeople(PersonRepository $personRepository): Response
    {
        $people = $personRepository->findCurrentForIndex();

        return $this->index($people);
    }

    #[Route('/people/all', name: 'person_allpeople')]
    public function allPeople(PersonRepository $personRepository): Response
    {
        $people = $personRepository->findAllForIndex();

        return $this->index($people);
    }

    /**
     * @param mixed $people
     * @return Response
     */
    protected function index(mixed $people): Response
    {
        $filterForm = $this->createForm(FilterType::class);

        return $this->render('person/index.html.twig', [
            'people' => $people,
            'filterForm' => $filterForm->createView(),
        ]);
    }

    #[Route('/person/{slug}', name: 'person_view', options: ['expose' => true])]
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
        $form = $this->createForm(PersonType::class, $person, [
            'show_position_when_joined' => $this->isGranted('ROLE_ADMIN'),
        ]);
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

    #[Route('/person/{slug}/update-last-review', name: 'person_update_last_review')]
    #[IsGranted('PERSON_EDIT', subject: 'person')]
    public function updateLastReviewed(
        Person $person,
        EntityManagerInterface $em,
        ActivityLogger $logger
    ): RedirectResponse {
        $person->setLastReviewedAt(new \DateTimeImmutable())
            ->setLastReviewedBy($this->getUser());
        $em->persist($person);

        $logger->log($person, 'Updated last review');

        $em->flush();

        return $this->redirectToRoute('person_view', ['slug' => $person->getSlug()]);
    }

    #[Route('/person/{slug}/add-theme-affiliation', name: 'person_add_theme_affiliation')]
    #[IsGranted('PERSON_EDIT', 'person')]
    public function newThemeAffiliation(
        Person $person,
        Request $request,
        HistoricityManager $historicityManager,
        EntityManagerInterface $em,
        ActivityLogger $logger
    ): Response {
        $themeAffiliation = (new ThemeAffiliation())
            ->setPerson($person);
        $form = $this->createForm(
            ThemeAffiliationType::class,
            $themeAffiliation,
            ['show_position_when_joined' => $this->isGranted('ROLE_ADMIN')]
        )
            ->add('endPreviousAffiliations', EntityType::class, [
                'required' => false,
                'mapped' => false,
                'multiple' => true,
                'expanded' => true,
                'class' => ThemeAffiliation::class,
                'choices' => $historicityManager->getCurrentEntities($person->getThemeAffiliations())->toArray(),
            ])
            ->add('add', SubmitType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($themeAffiliation);
            $logger->logNewAffiliation($themeAffiliation);
            foreach ($form->get('endPreviousAffiliations')->getData() as $endingAffiliation) {
                /** @var ThemeAffiliation $endingAffiliation */
                $endingAffiliation->setEndedAt($themeAffiliation->getStartedAt());
                $em->persist($endingAffiliation);
                $logger->logUpdatedAffiliation($endingAffiliation);
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
    #[IsGranted('PERSON_EDIT', 'person')]
    public function endThemeAffiliation(
        #[MapEntity(mapping: ['slug' => 'slug'])] Person $person,
        ThemeAffiliation $themeAffiliation,
        Request $request,
        EntityManagerInterface $em,
        ActivityLogger $logger
    ): Response {
        $form = $this->createForm(
            EndAffiliationType::class,
            $themeAffiliation,
            ['data_class' => ThemeAffiliation::class]
        );
        $form->add('exitReason', ExitReasonType::class)
            ->add('save', SubmitType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($themeAffiliation);
            $logger->logUpdatedAffiliation($themeAffiliation);
            $em->flush();

            return $this->redirectToRoute('person_view', ['slug' => $person->getSlug()]);
        }

        return $this->render('person/themeAffiliation/end.html.twig', [
            'person' => $person,
            'themeAffiliation' => $themeAffiliation,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/person/{slug}/edit-keys', name: 'person_edit_keys')]
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

    #[Route('/person/{slug}/add-room', name: 'person_add_room')]
    #[IsGranted("PERSON_EDIT", 'person')]
    public function addRoom(
        Person $person,
        Request $request,
        EntityManagerInterface $em,
        ActivityLogger $logger
    ): Response {
        $roomAffiliation = (new RoomAffiliation())
            ->setPerson($person);
        $form = $this->createForm(RoomAffiliationType::class, $roomAffiliation);
        $form->add('save', SubmitType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($roomAffiliation);
            $logger->logNewAffiliation($roomAffiliation);
            $em->flush();

            return $this->redirectToRoute('person_view', ['slug' => $person->getSlug()]);
        }

        return $this->render('person/room/add.html.twig', [
            'person' => $person,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/person/{slug}/room/{id}/end', name: 'person_end_room_affiliation')]
    public function endRoomAffiliation(
        #[MapEntity(mapping: ['slug' => 'slug'])] Person $person,
        RoomAffiliation $roomAffiliation,
        Request $request,
        EntityManagerInterface $em,
        ActivityLogger $logger
    ): Response {
        $this->denyAccessUnlessGranted('PERSON_EDIT', $person);
        $form = $this->createForm(
            EndAffiliationType::class,
            $roomAffiliation,
            ['data_class' => RoomAffiliation::class]
        );
        $form->add('save', SubmitType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($roomAffiliation);
            $logger->logUpdatedAffiliation($roomAffiliation);
            $em->flush();

            return $this->redirectToRoute('person_view', ['slug' => $person->getSlug()]);
        }

        return $this->render('person/room/end.html.twig', [
            'person' => $person,
            'roomAffiliation' => $roomAffiliation,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/_fragments/non-unique-error', name: 'person_nonuniqueerrorfragment')]
    public function nonUniqueErrorFragment(#[MapQueryParameter] int $id, PersonRepository $personRepository): Response
    {
        $person = $personRepository->find($id);
        // todo when we enable the workflow, show a different message when not logged in
        return $this->render('person/_nonUniqueError.html.twig', [
            'person' => $person,
        ]);
    }

    #[Route('/_fragments/search-results', name: 'person_searchresultsfragment', options: ['expose' => true])]
    public function searchResultsFragment(
        #[MapQueryParameter] string $query, PersonRepository $personRepository
    ): Response
    {
        $people = $personRepository->findByQuery($query);
        return $this->render('search_results.html.twig', [
            'people' => $people,
        ]);
    }
}
