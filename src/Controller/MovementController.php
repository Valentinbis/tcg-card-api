<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Movement;
use App\Entity\Recurrence;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
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
    
    #[Route('/api/movement', name: 'create_movement', methods: ['POST'])]
    public function createMovement(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        $createMovement = $this->serializer->deserialize(
            $request->getContent(),
            Movement::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => new Movement()]
        );

        $user = $this->getUser();
        $recurrence = $this->entityManager->getRepository(Recurrence::class)->find($data['recurrence']);
        $category = $this->entityManager->getRepository(Category::class)->find($data['category']);

        $createMovement->setUser($user);
        $createMovement->setRecurrence($recurrence);
        $createMovement->setCategory($category);

        $this->entityManager->persist($createMovement);
        $this->entityManager->flush();

        return new Response('Movement created!', Response::HTTP_CREATED);
    }

    #[Route('/api/movement/{id}', name: 'get_movement', methods: ['GET'])]
    public function getMovement($id): Response
    {
        $movement = $this->entityManager->getRepository(Movement::class)->find($id);

        if (!$movement) {
            throw $this->createNotFoundException('No movement found');
        }

        return $this->json($movement);
    }

    #[Route('/api/movement/{id}', name: 'update_movement', methods: ['PUT'])]
    public function updateMovement($id, Request $request): Response
    {
        $movement = $this->entityManager->getRepository(Movement::class)->find($id);

        if (!$movement) {
            throw $this->createNotFoundException('No movement found for id '.$id);
        }

        $data = json_decode($request->getContent(), true);
        $movement->setType($data['type']);
        $movement->setAmount($data['amount']);
        // update other fields...

        $this->entityManager->flush();

        return new Response('Movement updated!', Response::HTTP_OK);
    }

    #[Route('/api/movement/{id}', name: 'delete_movement', methods: ['DELETE'])]
    public function deleteMovement($id): Response
    {
        $movement = $this->entityManager->getRepository(Movement::class)->find($id);

        if (!$movement) {
            throw $this->createNotFoundException('No movement found for id '.$id);
        }

        $this->entityManager->remove($movement);
        $this->entityManager->flush();

        return new Response('Movement deleted!', Response::HTTP_OK);
    }
}
