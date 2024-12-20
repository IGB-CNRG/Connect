<?php
/*
 * Copyright (c) 2024 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Controller\Admin;

use App\Entity\Building;
use App\Entity\ExitReason;
use App\Entity\Faq;
use App\Entity\Key;
use App\Entity\KeyAffiliation;
use App\Entity\Log;
use App\Entity\MemberCategory;
use App\Entity\Person;
use App\Entity\Room;
use App\Entity\Setting;
use App\Entity\Theme;
use App\Entity\ThemeRole;
use App\Entity\ThemeType;
use App\Entity\Unit;
use App\Entity\WorkflowNotification;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
//        return parent::index();

        // Option 1. You can make your dashboard redirect to some common page of your backend
        //
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        if(in_array('ROLE_KEY_MANAGER', $this->getUser()->getRoles())){
            // The key manager probably wants to edit the keys
            return $this->redirect($adminUrlGenerator->setController(KeyCrudController::class)->generateUrl());
        } else {
            return $this->redirect($adminUrlGenerator->setController(ThemeCrudController::class)->generateUrl());
        }

        // Option 2. You can make your dashboard redirect to different pages depending on the user
        //
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirect('...');
        // }

        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        //
        // return $this->render('some/path/my-dashboard.html.twig');
        // TODO put some cool charts on this dashboard, or something
    }

    #[Route('/admin/workflow', name: 'admin_workflow_display')]
    public function workflowDisplay()
    {
        return $this->render('admin/workflows.html.twig');
    }

    public function configureAssets(): Assets
    {
        // todo will this break on production?
        return parent::configureAssets()->addCssFile('build/admin.css');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Connect')
            ->generateRelativeUrls()
            ->setFaviconPath("build/images/favicon.ico")
        ;
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToRoute('Back to Connect', 'fa fa-rotate-left', 'default');
        yield MenuItem::linkToCrud('Settings', 'fa fa-gear', Setting::class);
        yield MenuItem::linkToCrud('FAQs', 'fa fa-question', Faq::class);
        yield MenuItem::linkToCrud('Log', 'fa fa-book', Log::class);
        yield MenuItem::section('IGB');
        yield MenuItem::linkToCrud('All People', 'fas fa-list', Person::class)->setController(PersonCrudController::class);
        yield MenuItem::linkToCrud('New Units', 'fas fa-list', Person::class)->setController(OtherUnitCrudController::class);
        yield MenuItem::linkToCrud('Member Categories', 'fas fa-list', MemberCategory::class);
        yield MenuItem::linkToCrud('Keys', 'fas fa-list', Key::class);
        yield MenuItem::linkToCrud('Key Assignments', 'fas fa-list', KeyAffiliation::class);
        yield MenuItem::linkToCrud('Rooms', 'fas fa-list', Room::class);
        yield MenuItem::linkToCrud('Themes/Groups', 'fas fa-list', Theme::class);
        yield MenuItem::linkToCrud('Theme Roles', 'fas fa-list', ThemeRole::class);
        yield MenuItem::linkToCrud('Theme Types', 'fas fa-list', ThemeType::class);
        yield MenuItem::section('UIUC');
        yield MenuItem::linkToCrud('Buildings', 'fas fa-list', Building::class);
        yield MenuItem::linkToCrud('Units', 'fas fa-list', Unit::class);
        yield MenuItem::section('Workflows');
        yield MenuItem::linkToRoute('Workflow information', 'fas fa-diagram-project', 'admin_workflow_display');
        yield MenuItem::linkToCrud('Notifications', 'fas fa-envelope', WorkflowNotification::class);
        yield MenuItem::linkToCrud('Exit Reasons', 'fas fa-list', ExitReason::class);
    }
}
