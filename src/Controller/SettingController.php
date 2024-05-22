<?php
/*
 * Copyright (c) 2024 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Controller;

use App\Settings\SettingManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SettingController extends AbstractController
{
    #[Route('/settings', name: 'app_setting')]
    public function index(SettingManager $settingManager, Request $request, EntityManagerInterface $entityManager): Response
    {
        $success = false;
        // todo move this to config somehow
        $settings = [
            'default_index' => [
                'type' => ChoiceType::class,
                'options' => [
                    'choices' => [
                        "Current Members" => 'members',
                        "All Members Past/Present" => 'all_members',
                        "Current People" => 'people',
                        "All People Past/Present" => 'all_people',
                    ],
                ],
            ],
        ];
        // todo how can we move this form creation to the SettingsBundle?
        $builder = $this->createFormBuilder();
        foreach ($settings as $name => $settingConfig) {
            $options = $settingConfig['options'];
            $builder->add($name, $settingConfig['type'], [
                'label' => $settingManager->displayNameFromName($name),
                'required' => false,
                'choices' => $options['choices'],
                'data' => $settingManager->get($name, $this->getUser()),
            ]);
        }
        $builder->add('save', SubmitType::class);
        $form = $builder->getForm();

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            foreach($settings as $name => $settingConfig){
                $settingManager->set($name, $form->get($name)->getData(), $this->getUser());
            }
            $entityManager->flush();
            $success = true;
        }

        return $this->render('setting/index.html.twig', [
            'form' => $form->createView(),
            'success' => $success,
        ]);
    }
}
