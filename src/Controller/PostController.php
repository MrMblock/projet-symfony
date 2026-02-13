<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Form\CommentType;
use App\Form\PostType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/post')]
class PostController extends AbstractController
{
    #[Route('/', name: 'app_post_index')]
    public function index(PostRepository $postRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $query = $postRepository->findAllOrderedByPriorityQuery();
        
        $posts = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('post/index.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[Route('/search', name: 'app_post_search')]
    public function search(Request $request, PostRepository $postRepository): Response
    {
        $query = trim($request->query->getString('q'));
        $posts = [];

        if ($query !== '') {
            $posts = $postRepository->search($query);
        }

        return $this->render('post/search.html.twig', [
            'posts' => $posts,
            'query' => $query,
        ]);
    }

    #[Route('/new', name: 'app_post_new')]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setAuthor($this->getUser());
            $em->persist($post);
            $em->flush();

            $this->addFlash('success', 'Article cree avec succes.');
            return $this->redirectToRoute('app_post_show', ['slug' => $post->getSlug()]);
        }

        return $this->render('post/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{slug}', name: 'app_post_show')]
    public function show(#[MapEntity(mapping: ['slug' => 'slug'])] Post $post, Request $request, EntityManagerInterface $em): Response
    {
        $comment = new Comment();
        $commentForm = $this->createForm(CommentType::class, $comment);
        $commentForm->handleRequest($request);

        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $this->denyAccessUnlessGranted('ROLE_USER');
            $comment->setAuthor($this->getUser());
            $comment->setPost($post);
            $em->persist($comment);
            $em->flush();

            $this->addFlash('success', 'Commentaire ajoute.');
            return $this->redirectToRoute('app_post_show', ['slug' => $post->getSlug()]);
        }

        $post->incrementViews();
        $em->flush();

        return $this->render('post/show.html.twig', [
            'post' => $post,
            'commentForm' => $commentForm,
        ]);
    }

    #[Route('/{slug}/edit', name: 'app_post_edit')]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(#[MapEntity(mapping: ['slug' => 'slug'])] Post $post, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Article modifie avec succes.');
            return $this->redirectToRoute('app_post_show', ['slug' => $post->getSlug()]);
        }

        return $this->render('post/edit.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[Route('/{slug}/delete', name: 'app_post_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(#[MapEntity(mapping: ['slug' => 'slug'])] Post $post, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->getPayload()->getString('_token'))) {
            $em->remove($post);
            $em->flush();
            $this->addFlash('success', 'Article supprime.');
        }

        return $this->redirectToRoute('app_post_index');
    }

    #[Route('/{slug}/like', name: 'app_post_like', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function toggleLike(#[MapEntity(mapping: ['slug' => 'slug'])] Post $post, EntityManagerInterface $em, Request $request): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $liked = $user->toggleLike($post);
        $em->flush();

        if ($request->getPreferredFormat() === 'turbo_stream') {
            return $this->render('post/like_stream.html.twig', [
                'post' => $post,
                'liked' => $liked,
            ]);
        }

        $this->addFlash('success', $liked ? 'Article ajoute aux favoris.' : 'Article retire des favoris.');

        return $this->redirectToRoute('app_post_show', ['slug' => $post->getSlug()]);
    }
}
