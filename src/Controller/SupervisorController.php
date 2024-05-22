<?php
/*
 * Copyright (c) 2024 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Controller;

use App\Entity\Person;
use App\Entity\SponsorAffiliation;
use App\Entity\SupervisorAffiliation;
use App\Entity\ThemeAffiliation;
use App\Form\EndAffiliationType;
use App\Form\Person\SponsorType;
use App\Form\Person\SupervisorType;
use App\Log\ActivityLogger;
use App\Service\HistoricityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class SupervisorController extends AbstractController
{
    #[Route('/person/{slug}/theme-affiliation/{id}/add-supervisor', name: 'person_add_supervisor')]
    #[IsGranted("PERSON_EDIT", 'person')]
    public function addSupervisor(
        #[MapEntity(mapping: ['slug' => 'slug'])] Person $person,
        ThemeAffiliation $themeAffiliation,
        Request $request,
        EntityManagerInterface $em,
        HistoricityManager $historicityManager,
        ActivityLogger $logger
    ): Response {
        $supervisorAffiliation = (new SupervisorAffiliation())
            ->setSuperviseeThemeAffiliation($themeAffiliation)
        ;
        $form = $this->createForm(SupervisorType::class, $supervisorAffiliation)
            ->add('save', SubmitType::class);

        if($historicityManager->getCurrentEntities($themeAffiliation->getSupervisorAffiliations())->count() > 0){
            $form->add('endPreviousAffiliations', EntityType::class, [
                'required' => false,
                'mapped' => false,
                'multiple' => true,
                'expanded' => true,
                'class' => SupervisorAffiliation::class,
                'choices' => $historicityManager->getCurrentEntities($themeAffiliation->getSupervisorAffiliations())->toArray(),
                'choice_label' => 'supervisor',
            ]);
        }

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($supervisorAffiliation);
            $logger->logNewAffiliation($supervisorAffiliation);
            $em->flush();

            return $this->redirectToRoute('person_view', ['slug' => $person->getSlug()]);
        }

        return $this->render('person/supervisor/add.html.twig', [
            'person' => $person,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/person/{slug}/theme-affiliation/{id}/add-sponsor', name: 'person_add_sponsor')]
    #[IsGranted("PERSON_EDIT", 'person')]
    public function addSponsor(
        #[MapEntity(mapping: ['slug' => 'slug'])] Person $person,
        ThemeAffiliation $themeAffiliation,
        Request $request,
        EntityManagerInterface $em,
        HistoricityManager $historicityManager,
        ActivityLogger $logger
    ): Response {
        $sponsorAffiliation = (new SponsorAffiliation())
            ->setSponseeThemeAffiliation($themeAffiliation)
        ;
        $form = $this->createForm(SponsorType::class, $sponsorAffiliation)
            ->add('save', SubmitType::class);

        if($historicityManager->getCurrentEntities($themeAffiliation->getSponsorAffiliations())->count() > 0){
            $form->add('endPreviousAffiliations', EntityType::class, [
                'required' => false,
                'mapped' => false,
                'multiple' => true,
                'expanded' => true,
                'class' => SponsorAffiliation::class,
                'choices' => $historicityManager->getCurrentEntities($themeAffiliation->getSponsorAffiliations())->toArray(),
                'choice_label' => 'supervisor',
            ]);
        }

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($sponsorAffiliation);
            $logger->logNewAffiliation($sponsorAffiliation);
            $em->flush();

            return $this->redirectToRoute('person_view', ['slug' => $person->getSlug()]);
        }

        return $this->render('person/sponsor/add.html.twig', [
            'person' => $person,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/person/{slug}/supervisor/{id}/end', name: 'person_end_supervisor_affiliation')]
    public function endSupervisorAffiliation(
        #[MapEntity(mapping: ['slug' => 'slug'])] Person $person,
        SupervisorAffiliation $supervisorAffiliation,
        Request $request,
        EntityManagerInterface $em,
        ActivityLogger $logger
    ): Response {
        $this->denyAccessUnlessGranted('PERSON_EDIT', $person);
        $form = $this->createForm(EndAffiliationType::class, $supervisorAffiliation, [
            'data_class' => SupervisorAffiliation::class,
        ]);
        $form->add('save', SubmitType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($supervisorAffiliation);
            $logger->logUpdatedAffiliation($supervisorAffiliation);
            $em->flush();

            return $this->redirectToRoute('person_view', ['slug' => $person->getSlug()]);
        }

        return $this->render('person/supervisor/end.html.twig', [
            'person' => $person,
            'supervisorAffiliation' => $supervisorAffiliation,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/person/{slug}/sponsor/{id}/end', name: 'person_end_sponsor_affiliation')]
    public function endSponsorAffiliation(
        #[MapEntity(mapping: ['slug' => 'slug'])] Person $person,
        SponsorAffiliation $sponsorAffiliation,
        Request $request,
        EntityManagerInterface $em,
        ActivityLogger $logger
    ): Response {
        $this->denyAccessUnlessGranted('PERSON_EDIT', $person);
        $form = $this->createForm(EndAffiliationType::class, $sponsorAffiliation, [
            'data_class' => SponsorAffiliation::class,
        ]);
        $form->add('save', SubmitType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($sponsorAffiliation);
            $logger->logUpdatedAffiliation($sponsorAffiliation);
            $em->flush();

            return $this->redirectToRoute('person_view', ['slug' => $person->getSlug()]);
        }

        return $this->render('person/sponsor/end.html.twig', [
            'person' => $person,
            'sponsorAffiliation' => $sponsorAffiliation,
            'form' => $form->createView(),
        ]);
    }
}