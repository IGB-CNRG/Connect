<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Controller\Workflow;

use App\Entity\DigestBuffer;
use App\Repository\DigestBufferRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DigestController extends AbstractController
{
    #[Route('/digest/preview', name: 'app_workflow_digest_digestpreview')]
    public function digestPreview(DigestBufferRepository $digestBufferRepository): Response
    {
        $subject = 'Entry/Exit Digest';
        $entryDigests = $digestBufferRepository->findBy(['bufferName' => DigestBuffer::ENTRY_BUFFER]);
        $exitDigests = $digestBufferRepository->findBy(['bufferName' => DigestBuffer::EXIT_BUFFER]);
        return $this->render('workflow/digest/entry_exit.html.twig', [
            'subject' => $subject,
            'entries' => $entryDigests,
            'exits' => $exitDigests,
        ]);
    }

    #[Route('/digest/preview/txt', name: 'app_workflow_digest_digestpreviewtxt')]
    public function digestPreviewText(DigestBufferRepository $digestBufferRepository): Response
    {
        $subject = 'Entry/Exit Digest';
        $entryDigests = $digestBufferRepository->findBy(['bufferName' => DigestBuffer::ENTRY_BUFFER]);
        $exitDigests = $digestBufferRepository->findBy(['bufferName' => DigestBuffer::EXIT_BUFFER]);

        $response = $this->render('workflow/digest/entry_exit.txt.twig', [
            'subject' => $subject,
            'entries' => $entryDigests,
            'exits' => $exitDigests,
        ]);
        $response->headers->set('Content-Type', 'text/plain');
        return $response;
    }
}
