<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Controller;

use App\Entity\Person;
use App\Entity\RoomAffiliation;
use App\Entity\ThemeAffiliation;
use App\Entity\UnitAffiliation;
use App\Form\AdvancedSearchType;
use App\Form\EndRoomAffiliationType;
use App\Form\EndThemeAffiliationType;
use App\Form\EndUnitAffiliationType;
use App\Form\KeysType;
use App\Form\Person\PersonType;
use App\Form\Person\RoomAffiliationType;
use App\Form\Person\ThemeAffiliationType;
use App\Form\Person\UnitAffiliationType;
use App\Log\ActivityLogger;
use App\Repository\PersonRepository;
use App\Service\HistoricityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PersonController extends AbstractController
{
    #[Route('/', name: 'default')]
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
        $advancedSearchForm = $this->createForm(AdvancedSearchType::class);

        return $this->render('person/index.html.twig', [
            'people' => $people,
            'advancedSearchForm' => $advancedSearchForm->createView(),
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
        $form = $this->createForm(ThemeAffiliationType::class, $themeAffiliation)
            ->add('endPreviousAffiliations', EntityType::class, [
                'required' => false,
                'mapped' => false,
                'multiple' => true,
                'expanded' => true,
                'class' => ThemeAffiliation::class,
                'choices' => $historicityManager->getCurrentEntities($person->getThemeAffiliations())->toArray(),
            ])
            ->add('Add', SubmitType::class);

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
    #[IsGranted('PERSON_EDIT', 'person')]
    public function endThemeAffiliation(
        #[MapEntity(mapping: ['slug' => 'slug'])] Person $person,
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
            $logger->logNewRoomAffiliation($roomAffiliation);
            $em->flush();

            return $this->redirectToRoute('person_view', ['slug' => $person->getSlug()]);
        }

        return $this->render('person/room/add.html.twig', [
            'person' => $person,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/person/{slug}/add-unit', name: 'person_add_unit')]
    #[IsGranted("PERSON_EDIT", 'person')]
    public function addUnit(
        Person $person,
        Request $request,
        EntityManagerInterface $em,
        ActivityLogger $logger
    ): Response {
        $unitAffiliation = (new UnitAffiliation())
            ->setPerson($person);
        $form = $this->createForm(UnitAffiliationType::class, $unitAffiliation);
        $form->add('save', SubmitType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($unitAffiliation);
            $logger->logNewUnitAffiliation($unitAffiliation);
            $em->flush();

            return $this->redirectToRoute('person_view', ['slug' => $person->getSlug()]);
        }

        return $this->render('person/unit/add.html.twig', [
            'person' => $person,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/person/{slug}/unit/{id}/end', name: 'person_end_unit_affiliation')]
    public function endUnitAffiliation(
        #[MapEntity(mapping: ['slug' => 'slug'])] Person $person,
        UnitAffiliation $unitAffiliation,
        Request $request,
        EntityManagerInterface $em,
        ActivityLogger $logger
    ): Response {
        $this->denyAccessUnlessGranted('PERSON_EDIT', $person);
        $form = $this->createForm(EndUnitAffiliationType::class, $unitAffiliation);
        $form->add('save', SubmitType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($unitAffiliation);
            $logger->logEndUnitAffiliation($unitAffiliation);
            $em->flush();

            return $this->redirectToRoute('person_view', ['slug' => $person->getSlug()]);
        }

        return $this->render('person/unit/end.html.twig', [
            'person' => $person,
            'unitAffiliation' => $unitAffiliation,
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
        $form = $this->createForm(EndRoomAffiliationType::class, $roomAffiliation);
        $form->add('save', SubmitType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($roomAffiliation);
            $logger->logEndRoomAffiliation($roomAffiliation);
            $em->flush();

            return $this->redirectToRoute('person_view', ['slug' => $person->getSlug()]);
        }

        return $this->render('person/room/end.html.twig', [
            'person' => $person,
            'roomAffiliation' => $roomAffiliation,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/api/person', name: 'api_person_username', methods: ['GET'])]
    public function personJson(Request $request, PersonRepository $personRepository): JsonResponse
    {
        $username = $request->query->get('username');
        if ($username) {
            $person = $personRepository->findOneBy(['username' => $username]);
            if (!$person) {
                // todo we might want this to have a different default return value, later
                return $this->json([
                    'id' => 0,
                    'name' => '',
                    'slug' => '',
                ]);
            }

            return $this->json([
                'id' => $person->getId(),
                'name' => $person->getName(),
                'slug' => $person->getSlug(),
            ]);
        }
    }
}
