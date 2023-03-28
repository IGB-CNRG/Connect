<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Controller\Admin;

use App\Entity\Building;
use App\Entity\Key;
use App\Entity\KeyAffiliation;
use App\Entity\MemberCategory;
use App\Entity\Person;
use App\Entity\Room;
use App\Entity\Setting;
use App\Entity\Theme;
use App\Entity\Unit;
use App\Entity\UnitAffiliation;
use App\Entity\WorkflowNotification;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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

    public function configureAssets(): Assets
    {
        // todo will this break on production?
        return parent::configureAssets()->addCssFile('build/admin.css');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('CONNECT')
            ->generateRelativeUrls()
        ;
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToRoute('Back to CONNECT', 'fa fa-rotate-left', 'default');
        yield MenuItem::linkToCrud('Settings', 'fa fa-gear', Setting::class);
        yield MenuItem::section('IGB');
        yield MenuItem::linkToCrud('All People', 'fas fa-list', Person::class);
        yield MenuItem::linkToCrud('New Units', 'fas fa-list', UnitAffiliation::class);
        yield MenuItem::linkToCrud('Member Categories', 'fas fa-list', MemberCategory::class);
        yield MenuItem::linkToCrud('Keys', 'fas fa-list', Key::class);
        yield MenuItem::linkToCrud('Key Assignments', 'fas fa-list', KeyAffiliation::class);
        yield MenuItem::linkToCrud('Rooms', 'fas fa-list', Room::class);
        yield MenuItem::linkToCrud('Themes', 'fas fa-list', Theme::class);
        yield MenuItem::section('UIUC');
        yield MenuItem::linkToCrud('Buildings', 'fas fa-list', Building::class);
        yield MenuItem::linkToCrud('Units', 'fas fa-list', Unit::class);
        yield MenuItem::section('Workflows');
        yield MenuItem::linkToCrud('Notifications', 'fas fa-envelope', WorkflowNotification::class);
    }
}
