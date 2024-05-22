<?php
/*
 * Copyright (c) 2024 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Controller;

use App\Entity\Document;
use App\Entity\Person;
use App\Form\DocumentMetadataType;
use App\Form\DocumentType;
use App\Log\ActivityLogger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DocumentController extends AbstractController
{
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
            $logger->log($person, sprintf("Uploaded document '%s'", $document));
            $em->flush();

            return $this->redirectToRoute('person_view', ['slug' => $person->getSlug()]);
        }

        return $this->render('person/document/add.html.twig', [
            'person' => $person,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/person/{slug}/document/{id}/edit', name: 'person_edit_document')]
    #[IsGranted('PERSON_EDIT', subject: 'person')]
    public function editDocument(
        #[MapEntity(mapping: ['slug' => 'slug'])] Person $person,
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
            $logger->log($person, sprintf("Edited document '%s'", $document));
            $em->flush();

            return $this->redirectToRoute('person_view', ['slug' => $person->getSlug()]);
        }

        return $this->render('person/document/edit.html.twig', [
            'person' => $person,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/person/{slug}/document/{id}/delete', name: 'person_delete_document')]
    public function deleteDocument(
        #[MapEntity(mapping: ['slug' => 'slug'])] Person $person,
        Document $document,
        Request $request,
        EntityManagerInterface $em,
        ActivityLogger $logger
    ): Response {
        $this->denyAccessUnlessGranted('PERSON_EDIT', $person);

        if ($request->isMethod(Request::METHOD_POST)) {
            $em->remove($document);
            $logger->log($person, sprintf("Removed document '%s'", $document));
            $em->flush();

            return $this->redirectToRoute('person_view', ['slug' => $person->getSlug()]);
        }

        return $this->render('person/document/delete.html.twig', [
            'person' => $person,
            'document' => $document,
        ]);
    }
}