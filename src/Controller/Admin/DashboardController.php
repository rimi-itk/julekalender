<?php

namespace App\Controller\Admin;

use App\Entity\Calendar;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\CrudUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        $routeBuilder = $this->get(CrudUrlGenerator::class)->build();

        return $this->redirect($routeBuilder->setController(CalendarCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Calendar');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToCrud('Calendars', 'fa fa-user', Calendar::class);
        yield MenuItem::linkToCrud('Users', 'fa fa-user', User::class)
            ->setPermission('ROLE_ADMIN');
    }
}
