<?php

namespace App\Controller;

use App\Entity\Person;
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

    #[Route('/person/{id}', name: 'person_view')]
    public function view(Person $person): Response
    {
        return $this->render('person/view.html.twig', [
            'person' => $person,
        ]);
    }
}
