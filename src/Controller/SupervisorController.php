<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Controller;

use App\Entity\Person;
use App\Entity\SupervisorAffiliation;
use App\Form\EndSupervisorAffiliationType;
use App\Form\SuperviseeType;
use App\Form\SupervisorType;
use App\Log\ActivityLogger;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SupervisorController extends AbstractController
{
    #[Route('/person/{slug}/add-supervisee', name: 'person_add_supervisee')]
    #[IsGranted("PERSON_EDIT", 'person')]
    public function addSupervisee(
        Person $person,
        Request $request,
        EntityManagerInterface $em,
        ActivityLogger $logger
    ): Response {
        $supervisorAffiliation = (new SupervisorAffiliation())
            ->setSupervisor($person)
        ;
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

    #[Route('/person/{slug}/add-supervisor', name: 'person_add_supervisor')]
    #[IsGranted("PERSON_EDIT", 'person')]
    public function addSupervisor(
        Person $person,
        Request $request,
        EntityManagerInterface $em,
        ActivityLogger $logger
    ): Response {
        $supervisorAffiliation = (new SupervisorAffiliation())
            ->setSupervisee($person)
        ;
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

    #[Route('/person/{slug}/supervisor/{id}/end', name: 'person_end_supervisor_affiliation')]
    #[ParamConverter('person', options: ['mapping' => ['slug' => 'slug']])]
    public function endSupervisorAffiliation(
        Person $person,
        SupervisorAffiliation $supervisorAffiliation,
        Request $request,
        EntityManagerInterface $em,
        ActivityLogger $logger
    ): Response {
        $this->denyAccessUnlessGranted('PERSON_EDIT', $person);
        $form = $this->createForm(EndSupervisorAffiliationType::class, $supervisorAffiliation);
        $form->add('save', SubmitType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($supervisorAffiliation);
            $logger->logEndSupervisorAffiliation($supervisorAffiliation);
            $em->flush();

            return $this->redirectToRoute('person_view', ['slug' => $person->getSlug()]);
        }

        return $this->render('person/supervisor/end.html.twig', [
            'person' => $person,
            'supervisorAffiliation' => $supervisorAffiliation,
            'form' => $form->createView(),
        ]);
    }
}