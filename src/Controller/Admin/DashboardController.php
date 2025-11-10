<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\UserRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    private UserRepository $userRepository;
    private ParameterBagInterface $params;

    public function __construct(UserRepository $userRepository, ParameterBagInterface $params)
    {
        $this->userRepository = $userRepository;
        $this->params = $params;
    }

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig', [
            'countUsers' => $this->userRepository->countUsers(),
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
        $frontUrl = $this->params->get('front_url');
        $urlString = is_string($frontUrl) ? $frontUrl : '';
        yield MenuItem::linkToUrl('Retour sur le site', 'fa-solid fa-arrow-left', $urlString);
        yield MenuItem::linkToUrl('Logout', 'fa-solid fa-sign-out-alt', '/logout');
        yield MenuItem::section('Dashboard');
        yield MenuItem::linkToDashboard('Dashboard', 'fa-solid fa-chart-line');
        yield MenuItem::section('Tables');
        yield MenuItem::linkToCrud('Users', 'fas fa-user', User::class);
    }
}
