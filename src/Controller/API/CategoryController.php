<?php

namespace App\Controller\API;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class CategoryController extends AbstractController
{
    private $entityManager;
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    #[Route('/api/categories', name: 'list_categories', methods: ['GET'])]
    #[IsGranted("CATEGORY_LIST")]
    public function index(): Response
    {
        $categories = $this->entityManager->getRepository(Category::class)->findBy(['parent' => null]);

        $this->logger->info('Categories fetched successfully', ['count' => count($categories)]);
        
        return $this->json($categories, Response::HTTP_OK, [], [
            'groups' => ['category.show']
        ]);
    }
    
    #[Route('/api/categories/parents', name: 'list_categories_parents', methods: ['GET'])]
    #[IsGranted("CATEGORY_LIST")]
    public function showAllParent(): Response
    {
        $categories = $this->entityManager->getRepository(Category::class)->findBy(['parent' => null]);

        $this->logger->info('Parent categories fetched successfully', ['count' => count($categories)]);

        return $this->json($categories, Response::HTTP_OK, [], [
            'groups' => ['category.show']
        ]);
    }

    #[Route('/api/category/{category}/children', name: 'list_category_children', methods: ['GET'])]
    #[IsGranted("CATEGORY_LIST")]
    public function showChildren(Category $category): Response
    {
        $categories = $this->entityManager->getRepository(Category::class)->findBy(['parent' => $category]);

        $this->logger->info('Children categories fetched successfully', ['count' => count($categories)]);

        return $this->json($categories, Response::HTTP_OK, [], [
            'groups' => ['category.show']
        ]);
    }

    #[Route('/api/category/{category}', name: 'show_category', methods: ['GET'])]
    #[IsGranted("CATEGORY_VIEW", subject: "category")]
    public function show(Category $category): Response
    {
        $this->logger->info('Fetching category details', ['category_id' => $category->getId()]);

        return $this->json($category, Response::HTTP_OK, [], [
            'groups' => ['category.show']
        ]);
    }

    #[Route('/api/category', name: 'create_category', methods: ['POST'])]
    #[IsGranted("CATEGORY_EDIT", subject: "category")]
    public function create(
        #[MapRequestPayload(serializationContext: [
            'groups' => ['category.create']
        ])]
        Category $category
    ): Response {

        $this->entityManager->persist($category);
        $this->entityManager->flush();
        $this->logger->info('Category created successfully', ['category_id' => $category->getId()]);

        return new Response('Category created!', Response::HTTP_CREATED);
    }

    #[Route('/api/category/{category}', name: 'update_category', methods: ['PATCH'])]
    #[IsGranted("CATEGORY_EDIT", subject: "category")]
    public function update(Request $request, Category $category, SerializerInterface $serializer): Response
    {
        $updatedCategory = $serializer->deserialize(
            $request->getContent(),
            Category::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $category]
        );

        $this->entityManager->persist($updatedCategory);
        $this->entityManager->flush();
        $this->logger->info('Category updated successfully', ['category_id' => $updatedCategory->getId()]);

        return new Response('Category updated!', Response::HTTP_OK);
    }

    #[Route('/api/category/{category}', name: 'delete_category', methods: ['DELETE'])]
    #[IsGranted("CATEGORY_EDIT", subject: "category")]
    public function delete(Category $category): Response
    {
        $this->entityManager->remove($category);
        $this->entityManager->flush();
        $this->logger->info('Category deleted successfully', ['category_id' => $category->getId()]);

        return new Response('Category deleted!', Response::HTTP_NO_CONTENT);
    }
}
