<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\Movement;
use App\Entity\Recurrence;
use App\Entity\User;
use App\Repository\UserRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig', [
            'countUsers' => $this->userRepository->countUsers()
        ]);

    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Administration');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::section('Menu');
        yield MenuItem::linkToRoute('Back to the website', 'fa-solid fa-arrow-left', 'home');
        yield MenuItem::linkToRoute('Logout', 'fa-solid fa-sign-out-alt', 'app_logout');
        yield MenuItem::section('Dashboard');
        yield MenuItem::linkToDashboard('Dashboard', 'fa-solid fa-chart-line');
        yield MenuItem::section('Tables');
        yield MenuItem::linkToCrud('Users', 'fas fa-user', User::class);
        yield MenuItem::linkToCrud('Category', 'fas fa-list', Category::class);
        yield MenuItem::linkToCrud('Transaction', 'fas fa-money-bill-wave', Movement::class);
        yield MenuItem::linkToCrud('Recurrence', 'fas fa-redo-alt', Recurrence::class);
    }
}
