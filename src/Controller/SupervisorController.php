<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Controller;

use App\Entity\Person;
use App\Entity\SupervisorAffiliation;
use App\Form\EndSupervisorAffiliationType;
use App\Form\Person\SuperviseeType;
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
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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
        $form = $this->createForm(SuperviseeType::class, $supervisorAffiliation)
            ->add('save', SubmitType::class);

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
        HistoricityManager $historicityManager,
        ActivityLogger $logger
    ): Response {
        $supervisorAffiliation = (new SupervisorAffiliation())
            ->setSupervisee($person)
        ;
        $form = $this->createForm(SupervisorType::class, $supervisorAffiliation)
            ->add('endPreviousAffiliations', EntityType::class, [
                'required' => false,
                'mapped' => false,
                'multiple' => true,
                'expanded' => true,
                'class' => SupervisorAffiliation::class,
                'choices' =>$historicityManager->getCurrentEntities($person->getSupervisorAffiliations())->toArray(),
                'choice_label' => 'supervisor',
            ])
            ->add('save', SubmitType::class);

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
    public function endSupervisorAffiliation(
        #[MapEntity(mapping: ['slug' => 'slug'])] Person $person,
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