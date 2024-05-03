<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Movement;
use App\Entity\Recurrence;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class MovementController extends AbstractController
{
    private $entityManager;
    private $serializer;

    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer)
    {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    #[Route('/api/movements', name: 'list_movements', methods: ['GET'])]
    #[IsGranted("ROLE_USER")]
    #[IsGranted("MOVEMENT_VIEW", subject: "movement")]
    public function index(): Response
    {
        $movements = $this->entityManager->getRepository(Movement::class)->findAll();

        return $this->json($movements, Response::HTTP_OK, [], [
            'groups' => ['movements.show']
        ]);
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
        $movement->setRecurrence($this->entityManager->getRepository(Recurrence::class)->find($data['recurrence']));
        $movement->setCategory($this->entityManager->getRepository(Category::class)->find($data['category']));

        $this->entityManager->persist($movement);
        $this->entityManager->flush();

        return new Response('Movement created!', Response::HTTP_CREATED);
    }

    #[Route('/api/movement/{id}', name: 'show_movement', methods: ['GET'])]
    #[IsGranted("ROLE_USER")]
    #[IsGranted("MOVEMENT_VIEW", subject: "movement")]
    public function show(Movement $movement): Response
    {
        if (!$movement) {
            throw $this->createNotFoundException('No movement found');
        }

        return $this->json($movement, Response::HTTP_OK, [], [
            'groups' => ['movements.show']
        ]);
    }

    #[Route('/api/movement/{id}', name: 'update_movement', methods: ['PUT'])]
    #[IsGranted("ROLE_USER")]
    #[IsGranted("MOVEMENT_EDIT", subject: "movement")]
    public function update(
        ?Movement $movement,
        Request $request
    ): Response {
        if (!$movement) {
            throw $this->createNotFoundException('No movement found');
        }

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

        if (isset($data['recurrence'])) {
            $recurrence = $this->getEntity(Recurrence::class, $data['recurrence']);
            $updatedMovement->setRecurrence($recurrence);
        }

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

    #[Route('/api/movement/{id}', name: 'delete_movement', methods: ['DELETE'])]
    #[IsGranted("ROLE_USER")]
    #[IsGranted("MOVEMENT_EDIT", subject: "movement")]
    public function delete(Movement $movement): Response
    {
        if (!$movement) {
            throw $this->createNotFoundException('No movement found');
        }

        $this->entityManager->remove($movement);
        $this->entityManager->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    private function getEntity($repository, $id)
    {
        $entity = $this->entityManager->getRepository($repository)->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No entity found');
        }
        return $entity;
    }
}
