<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\CardRepository;
use App\Repository\CollectionRepository;
use App\Repository\PriceRepository;
use App\Repository\SetRepository;
use App\Repository\UserRepository;
use App\Repository\WishlistRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    private UserRepository $userRepository;
    private CardRepository $cardRepository;
    private SetRepository $setRepository;
    private CollectionRepository $collectionRepository;
    private WishlistRepository $wishlistRepository;
    private PriceRepository $priceRepository;
    private ParameterBagInterface $params;

    public function __construct(
        UserRepository $userRepository,
        CardRepository $cardRepository,
        SetRepository $setRepository,
        CollectionRepository $collectionRepository,
        WishlistRepository $wishlistRepository,
        PriceRepository $priceRepository,
        ParameterBagInterface $params
    ) {
        $this->userRepository = $userRepository;
        $this->cardRepository = $cardRepository;
        $this->setRepository = $setRepository;
        $this->collectionRepository = $collectionRepository;
        $this->wishlistRepository = $wishlistRepository;
        $this->priceRepository = $priceRepository;
        $this->params = $params;
    }

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig', [
            'countUsers' => $this->userRepository->countUsers(),
            'countCards' => $this->cardRepository->count([]),
            'countSets' => $this->setRepository->count([]),
            'countCollections' => $this->collectionRepository->count([]),
            'countWishlists' => $this->wishlistRepository->count([]),
            'countPrices' => $this->priceRepository->count([]),
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
        yield MenuItem::linkToCrud('Cards', 'fas fa-clone', \App\Entity\Card::class);
        yield MenuItem::linkToCrud('Sets', 'fas fa-layer-group', \App\Entity\Set::class);
        yield MenuItem::linkToCrud('Collections', 'fas fa-boxes', \App\Entity\Collection::class);
        yield MenuItem::linkToCrud('Wishlists', 'fas fa-heart', \App\Entity\Wishlist::class);
        yield MenuItem::linkToCrud('Prices', 'fas fa-dollar-sign', \App\Entity\Price::class);
    }
}
