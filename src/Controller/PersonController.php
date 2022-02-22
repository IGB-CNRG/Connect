<?php

namespace App\Controller;

use App\Repository\PersonRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
}
