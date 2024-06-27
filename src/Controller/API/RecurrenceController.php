<?php

namespace App\Controller\API;

use App\Enums\RecurrenceEnum;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RecurrenceController extends AbstractController
{
    #[Route('/api/recurrence', name: 'list_recurrence', methods: ['GET'])]
    public function index(): Response
    {
        $recurrences = RecurrenceEnum::cases();
        $recurrenceValues = array_map(fn($case) => $case->value, $recurrences);

        return $this->json(array_values($recurrenceValues), Response::HTTP_OK);
    }
}
