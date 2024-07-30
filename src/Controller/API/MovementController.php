<?php

namespace App\Controller\API;

use App\DTO\MovementFilterDTO;
use App\DTO\PaginationDTO;
use App\Entity\Category;
use App\Entity\Movement;
use App\Service\RecurrenceService;
use Carbon\CarbonImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class MovementController extends AbstractController
{
    private $entityManager;
    private $serializer;
    private $recurrenceService;

    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer, RecurrenceService $recurrenceService)
    {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->recurrenceService = $recurrenceService;
    }

    #[Route('/api/movements', name: 'list_movements', methods: ['GET'])]
    #[IsGranted("MOVEMENT_LIST")]
    public function index(
        #[MapQueryParameter] ?string $type,
        #[MapQueryParameter] ?int $categoryId,
        #[MapQueryParameter] ?string $startDate,
        #[MapQueryParameter] ?string $endDate,
        #[MapQueryParameter] ?int $page,
        #[MapQueryParameter] ?int $limit,
        #[MapQueryParameter] ?string $sort,
        #[MapQueryParameter] ?string $order
    ): Response {
        $user = $this->getUser();

        $startDateCarbon = $startDate ? new CarbonImmutable($startDate) : null;
        $endDateCarbon = $endDate ? new CarbonImmutable($endDate) : null;


        $movements = $this->entityManager->getRepository(Movement::class)->findByCriteria(
            new MovementFilterDTO($user->getId(), $type, $categoryId, $startDateCarbon, $endDateCarbon),
            new PaginationDTO($page, $limit, $sort, $order)
        );

        return $this->json($movements, Response::HTTP_OK, [], [
            'groups' => ['movements.show']
        ]);
    }

    #[Route('/api/movements-by-categories', name: 'list_movements_by_categories', methods: ['GET'])]
    public function showByCategory(
        #[MapQueryParameter] ?string $type
    ): Response {
        $user = $this->getUser();

        $movements = $this->entityManager->getRepository(Movement::class)->findGroupByCategories($user->getId(), $type);

        $totalAmount = array_reduce($movements, function ($sum, $movement) {
            return $sum + abs((float)$movement['total']);
        }, 0);
        // Calculer les pourcentages pour chaque catégorie
        $percentages = array_map(function ($movement) use ($totalAmount) {
            $amount = abs((float)$movement['total']);
            $percentage = ($totalAmount > 0) ? ($amount / $totalAmount) * 100 : 0;
            return [
                'category' => $movement['category'],
                'total' => $movement['total'],
                'percentage' => round($percentage)
            ];
        }, $movements);

        return $this->json($percentages, Response::HTTP_OK);
    }

    #[Route('/api/movement', name: 'create_movement', methods: ['POST'])]
    #[IsGranted("ROLE_USER")]
    #[IsGranted("MOVEMENT_CREATE")]
    public function create(
        Request $request,
        #[MapRequestPayload(
            serializationContext: [
                'groups' => ['movements.create']
            ]
        )]
        Movement $movement
    ): Response {
        $data = json_decode($request->getContent(), true);

        $movement->setUser($this->getUser());
        $this->recurrenceService->createRecurrence($movement, $data);
        $movement->setCategory($this->entityManager->getRepository(Category::class)->find($data['category']));

        $this->entityManager->persist($movement);
        $this->entityManager->flush();

        return new Response('Movement created!', Response::HTTP_CREATED);
    }

    #[Route('/api/movement/{movement}', name: 'show_movement', methods: ['GET'])]
    #[IsGranted("ROLE_USER")]
    #[IsGranted("MOVEMENT_VIEW", subject: "movement")]
    public function show(?Movement $movement): Response
    {
        return $this->json($movement, Response::HTTP_OK, [], [
            'groups' => ['movements.show']
        ]);
    }

    #[Route('/api/movement/{movement}', name: 'update_movement', methods: ['PATCH'])]
    #[IsGranted("ROLE_USER")]
    #[IsGranted("MOVEMENT_EDIT", subject: "movement")]
    public function update(
        Movement $movement,
        Request $request
    ): Response {
        $data = json_decode($request->getContent(), true);
        $updatedMovement = $this->serializer->deserialize(
            $request->getContent(),
            Movement::class,
            'json',
            [
                AbstractNormalizer::OBJECT_TO_POPULATE => $movement,
                AbstractNormalizer::IGNORED_ATTRIBUTES => ['recurrence', 'category']
            ]
        );

        $this->recurrenceService->updateRecurrence($updatedMovement, $data);

        if (isset($data['category'])) {
            $category = $this->getEntity(Category::class, $data['category']);
            $updatedMovement->setCategory($category);
        }

        $this->entityManager->persist($updatedMovement);
        $this->entityManager->flush();

        return $this->json($updatedMovement, Response::HTTP_OK, [], [
            'groups' => ['movements.show']
        ]);
    }

    #[Route('/api/movement/{movement}', name: 'delete_movement', methods: ['DELETE'])]
    #[IsGranted("ROLE_USER")]
    #[IsGranted("MOVEMENT_EDIT", subject: "movement")]
    public function delete(Movement $movement): Response
    {
        $this->entityManager->remove($movement);
        $this->entityManager->flush();

        return new Response('Movement deleted!', Response::HTTP_OK);
    }

    private function getEntity($repository, $id)
    {
        $entity = $this->entityManager->getRepository($repository)->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No entity found');
        }
        return $entity;
    }

    #[Route('/api/movements/total-between-dates', name: 'total_movements_between_dates', methods: ['GET'])]
    public function showTotalBetweenDates(
        #[MapQueryParameter] string $startDate,
        #[MapQueryParameter] string $endDate,
    ): Response {
        $user = $this->getUser();
        $startDateCarbon = new CarbonImmutable($startDate);
        $endDateCarbon = new CarbonImmutable($endDate);

        $total = $this->entityManager->getRepository(Movement::class)->calculateTotalBetweenDates(
            $user->getId(),
            $startDateCarbon->startOfMonth()->format('Y-m-d H:i:s'),
            $endDateCarbon->endOfMonth()->format('Y-m-d H:i:s')
        );

        return $this->json($total, Response::HTTP_OK);
    }

    #[Route('/api/movements/total', name: 'total_movements', methods: ['GET'])]
    public function showTotal(): Response
    {
        $user = $this->getUser();

        $total = $this->entityManager->getRepository(Movement::class)->calculateTotal($user->getId());

        return $this->json($total, Response::HTTP_OK);
    }

    #[Route('/api/movements/total-yearly-by-month', name: 'total_movements_yearly_by_month', methods: ['GET'])]
    public function showTotalYearlyByMonth(
        #[MapQueryParameter] string $year
    ): Response
    {
        $user = $this->getUser();

        $total = $this->entityManager->getRepository(Movement::class)->calculateTotalYearlyByMonth($user->getId(), $year);

        return $this->json($total, Response::HTTP_OK);
    }
}
