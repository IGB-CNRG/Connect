<?php

namespace App\Controller;

use App\Entity\Person;
use App\Entity\ThemeAffiliation;
use App\Form\EndThemeAffiliationType;
use App\Form\PersonType;
use App\Form\ThemeAffiliationType;
use App\Repository\PersonRepository;
use App\Service\ActivityLogger;
use Doctrine\ORM\EntityManagerInterface;
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

    #[Route('/person/{id}', name: 'person_view')]
    public function view(Person $person): Response
    {
        return $this->render('person/view.html.twig', [
            'person' => $person,
        ]);
    }

    #[Route('/person/{id}/edit', name: 'person_edit')]
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

            return $this->redirectToRoute('person_view', ['id' => $person->getId()]);
        }
        return $this->render('person/edit.html.twig', [
            'person' => $person,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/person/new', name: 'person_new', priority: 1)]
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

            return $this->redirectToRoute('person_view', ['id' => $person->getId()]);
        }
        return $this->render('person/new.html.twig', [
            'person' => $person,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/person/{id}/add-theme-affiliation', name: 'person_new_theme_affiliation')]
    public function newThemeAffiliation(
        Person $person,
        Request $request,
        EntityManagerInterface $em,
        ActivityLogger $logger
    ): Response {
        $themeAffiliation = (new ThemeAffiliation())
            ->setPerson($person);
        $form = $this->createForm(ThemeAffiliationType::class, $themeAffiliation, ['person'=>$person]);
        $form->add('Add', SubmitType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($themeAffiliation);
            $logger->logNewThemeAffiliation($themeAffiliation);
            foreach ($form->get('endPreviousAffiliations')->getData() as $endingAffiliation){
                /** @var ThemeAffiliation $endingAffiliation */
                $endingAffiliation->setEndedAt($themeAffiliation->getStartedAt());
                $em->persist($endingAffiliation);
                $logger->logEndThemeAffiliation($endingAffiliation);
            }

            $em->flush();

            return $this->redirectToRoute('person_view', ['id' => $person->getId()]);
        }

        return $this->render('person/addThemeAffiliation.html.twig', [
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

            return $this->redirectToRoute('person_view', ['id' => $themeAffiliation->getPerson()->getId()]);
        }

        return $this->render('person/endThemeAffiliation.html.twig', [
            'person' => $themeAffiliation->getPerson(),
            'themeAffiliation' => $themeAffiliation,
            'form' => $form->createView(),
        ]);
    }
}
