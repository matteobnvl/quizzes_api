<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\QuizRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class CategoryController extends AbstractController
{
    #[Route('/api/category/{id_user}', name: 'app_category', methods:['GET'])]
    public function getCategoryByUser(CategoryRepository $categoryRepository, SerializerInterface $serializer, int $id_user): JsonResponse
    {
        $category = $categoryRepository->findBy(['user' => $id_user]);

        $json = $serializer->serialize($category, 'json');

        return new JsonResponse($json, JsonResponse::HTTP_CREATED);
    }

    #[Route('/api/category/{id_user}/{id_category}', name: 'app_category_user', methods:['GET'])]
    public function getCategoryByUserAndCategory(
        CategoryRepository $categoryRepository, 
        SerializerInterface $serializer, 
        int $id_user,
        int $id_category): JsonResponse
    {
        $category = $categoryRepository->findOneBy(['user' => $id_user, 'id' => $id_category]);

        $json = $serializer->serialize($category, 'json');

        return new JsonResponse($json, JsonResponse::HTTP_CREATED);
    }

    #[Route('/api/category/{id_user}/{id_category}/update', name: 'app_category_update', methods:['PUT'])]
    public function updateCategoryByUserAndCategory(
        Request $request,
        CategoryRepository $categoryRepository, 
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        int $id_user,
        int $id_category): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $category = $categoryRepository->findOneBy(['user' => $id_user, 'id' => $id_category]);
        $category->setName($data['category_name']);
        $entityManager->flush();

        $json = $serializer->serialize($category, 'json');

        return new JsonResponse($json, JsonResponse::HTTP_CREATED);
    }

    #[Route('/api/category/quiz/{category_id}', name: 'app_quiz_category', methods: ['POST'])]
    public function getQuizByCategory(QuizRepository $quizRepository, int $category_id, SerializerInterface $serializer): JsonResponse
    {
        $quizzes = $quizRepository->findBy(['category' => $category_id]);
        $json = $serializer->serialize($quizzes, 'json');

        return new JsonResponse($json, JsonResponse::HTTP_CREATED);
    }
}
