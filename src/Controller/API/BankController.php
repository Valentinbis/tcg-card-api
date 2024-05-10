<?php

namespace App\Controller\API;

use App\Entity\Bank;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class BankController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    #[Route('/api/banks', name: 'list_bank', methods: ['GET'])]
    #[IsGranted("BANK_VIEW")]
    public function index(): Response
    {
        $banks = $this->entityManager->getRepository(Bank::class)->findAll();

        return $this->json($banks, Response::HTTP_OK, [], [
            'groups' => ['bank.show']
        ]);
    }

    #[Route('/api/bank/{id}', name: 'show_bank')]
    #[IsGranted("BANK_VIEW", subject: "bank")]
    public function show(Bank $bank): Response
    {
        return $this->json($bank, Response::HTTP_OK, [], [
            'groups' => ['bank.show']
        ]);
    }
}
