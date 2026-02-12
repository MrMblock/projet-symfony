<?php

namespace App\Controller\Api;

use App\Entity\Category;
use App\Entity\Post;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
class ApiPostController extends AbstractController
{
    #[Route('/posts', name: 'api_posts_list', methods: ['GET'])]
    public function list(PostRepository $postRepository): JsonResponse
    {
        $posts = $postRepository->findAllOrderedByPriority();

        $data = array_map(function (Post $post): array {
            return [
                'id' => $post->getId(),
                'title' => $post->getTitle(),
                'slug' => $post->getSlug(),
                'content' => $post->getContent(),
                'picture' => $post->getPicture(),
                'publishedAt' => $post->getPublishedAt()?->format('c'),
                'priority' => $post->getPriority(),
                'category' => $post->getCategory() ? [
                    'id' => $post->getCategory()->getId(),
                    'name' => $post->getCategory()->getName(),
                ] : null,
                'author' => [
                    'id' => $post->getAuthor()?->getId(),
                    'firstName' => $post->getAuthor()?->getFirstName(),
                    'lastName' => $post->getAuthor()?->getLastName(),
                ],
                'commentsCount' => $post->getComments()->count(),
            ];
        }, $posts);

        return $this->json($data);
    }

    #[Route('/posts', name: 'api_posts_create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function create(Request $request, EntityManagerInterface $em, CategoryRepository $categoryRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['title'], $data['content'], $data['category_id'])) {
            return $this->json(['message' => 'Missing required fields (title, content, category_id)'], 400);
        }

        $category = $categoryRepository->find($data['category_id']);
        if (!$category) {
            return $this->json(['message' => 'Category not found'], 404);
        }

        $post = new Post();
        $post->setTitle($data['title']);
        $post->setContent($data['content']);
        $post->setCategory($category);
        $post->setPriority($data['priority'] ?? 0);
        $post->setPicture($data['picture'] ?? null);
        $post->setAuthor($this->getUser());

        $em->persist($post);
        $em->flush();

        return $this->json([
            'message' => 'Article created successfully',
            'id' => $post->getId(),
            'slug' => $post->getSlug(),
        ], 201);
    }

    #[Route('/posts/{slug}', name: 'api_post_show', methods: ['GET'])]
    public function show(#[MapEntity(mapping: ['slug' => 'slug'])] Post $post): JsonResponse
    {
        $data = [
            'id' => $post->getId(),
            'title' => $post->getTitle(),
            'slug' => $post->getSlug(),
            'content' => $post->getContent(),
            'picture' => $post->getPicture(),
            'publishedAt' => $post->getPublishedAt()?->format('c'),
            'priority' => $post->getPriority(),
            'category' => $post->getCategory() ? [
                'id' => $post->getCategory()->getId(),
                'name' => $post->getCategory()->getName(),
                'description' => $post->getCategory()->getDescription(),
            ] : null,
            'author' => [
                'id' => $post->getAuthor()?->getId(),
                'firstName' => $post->getAuthor()?->getFirstName(),
                'lastName' => $post->getAuthor()?->getLastName(),
            ],
            'comments' => $post->getComments()->map(fn ($comment) => [
                'id' => $comment->getId(),
                'content' => $comment->getContent(),
                'createdAt' => $comment->getCreatedAt()?->format('c'),
                'author' => [
                    'id' => $comment->getAuthor()?->getId(),
                    'firstName' => $comment->getAuthor()?->getFirstName(),
                    'lastName' => $comment->getAuthor()?->getLastName(),
                ],
            ])->toArray(),
        ];

        return $this->json($data);
    }
}
