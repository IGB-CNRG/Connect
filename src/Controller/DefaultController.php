<?php
/*
 * Copyright (c) 2024 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Controller;

use App\Repository\FaqRepository;
use App\Settings\SettingManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'default')]
    public function index(SettingManager $settingManager): RedirectResponse
    {
        $defaultRoute = match($settingManager->get('default_index', $this->getUser())){
            'all_members' => 'person_allmembers',
            'people' => 'person_currentpeople',
            'all_people' => 'person_allpeople',
            default => 'person_currentmembers',
        };
        return $this->redirectToRoute($defaultRoute);
    }

    #[Route('/copyright', name: 'copyright')]
    public function copyright()
    {
        return $this->render('default/copyright.html.twig');
    }

    #[Route('/faqs', name: 'faqs')]
    public function faqs(FaqRepository $repository)
    {
        $faqs = $repository->findAllOrdered();
        return $this->render('default/faqs.html.twig', [
            'faqs' => $faqs,
        ]);
    }
}
