<?php

namespace App\Controller\API;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

// Uniquement disponible pour les utilisateurs ayant le rôle ROLE_ADMIN
// Peut être à ajouter plus tard une liste de categories par default(ou fait par un admin) et
// la possibilité aux utilisateurs de créer des categories
class CategoryController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/api/categories', name: 'list_categories', methods: ['GET'])]
    #[IsGranted("CATEGORY_LIST")]
    public function index(): Response
    {
        $categories = $this->entityManager->getRepository(Category::class)->findAll();
        return $this->json($categories, Response::HTTP_OK, [], [
            'groups' => ['category.show']
        ]);
    }

    #[Route('/api/categories/parents', name: 'list_categories_parents', methods: ['GET'])]
    #[IsGranted("CATEGORY_LIST")]
    public function showAllParent(): Response
    {
        $categories = $this->entityManager->getRepository(Category::class)->findBy(['parent' => null]);
        return $this->json($categories, Response::HTTP_OK, [], [
            'groups' => ['category.show']
        ]);
    }

    #[Route('/api/category/{category}/children', name: 'list_category_children', methods: ['GET'])]
    #[IsGranted("CATEGORY_LIST")]
    public function showChildren(Category $category): Response
    {
        $categories = $this->entityManager->getRepository(Category::class)->findBy(['parent' => $category]);
        return $this->json($categories, Response::HTTP_OK, [], [
            'groups' => ['category.show']
        ]);
    }

    #[Route('/api/category/{category}', name: 'show_category', methods: ['GET'])]
    #[IsGranted("CATEGORY_VIEW", subject: "category")]
    public function show(Category $category): Response
    {
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

        return new Response('Category updated!', Response::HTTP_OK);
    }

    #[Route('/api/category/{category}', name: 'delete_category', methods: ['DELETE'])]
    #[IsGranted("CATEGORY_EDIT", subject: "category")]
    public function delete(Category $category): Response
    {
        $this->entityManager->remove($category);
        $this->entityManager->flush();

        return new Response('Category deleted!', Response::HTTP_NO_CONTENT);
    }
}
