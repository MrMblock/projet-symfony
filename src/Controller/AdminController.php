<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\User;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'app_admin_dashboard')]
    public function dashboard(PostRepository $postRepo, UserRepository $userRepo, CommentRepository $commentRepo, CategoryRepository $categoryRepo): Response
    {
        return $this->render('admin/dashboard.html.twig', [
            'totalPosts' => $postRepo->count(),
            'totalUsers' => $userRepo->count(),
            'totalComments' => $commentRepo->count(),
            'totalCategories' => $categoryRepo->count(),
            'unreadComments' => $commentRepo->countUnread(),
        ]);
    }

    #[Route('/users', name: 'app_admin_users')]
    public function users(UserRepository $userRepository): Response
    {
        return $this->render('admin/users.html.twig', [
            'users' => $userRepository->findBy([], ['createdAt' => 'DESC']),
        ]);
    }

    #[Route('/user/{id}/toggle', name: 'app_admin_user_toggle', methods: ['POST'])]
    public function toggleUser(User $user, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('toggle'.$user->getId(), $request->getPayload()->getString('_token'))) {
            $roles = $user->getRoles();
            if (in_array('ROLE_ADMIN', $roles)) {
                $user->setRoles([]);
            } else {
                $user->setRoles(['ROLE_ADMIN']);
            }
            $em->flush();
            $this->addFlash('success', 'Role utilisateur modifie.');
        }

        return $this->redirectToRoute('app_admin_users');
    }

    #[Route('/categories', name: 'app_admin_categories')]
    public function categories(CategoryRepository $categoryRepository): Response
    {
        return $this->render('admin/categories.html.twig', [
            'categories' => $categoryRepository->findAll(),
        ]);
    }

    #[Route('/category/new', name: 'app_admin_category_new')]
    public function newCategory(Request $request, EntityManagerInterface $em): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($category);
            $em->flush();
            $this->addFlash('success', 'Categorie creee.');
            return $this->redirectToRoute('app_admin_categories');
        }

        return $this->render('admin/category_form.html.twig', [
            'form' => $form,
            'title' => 'Nouvelle categorie',
        ]);
    }

    #[Route('/category/{id}/edit', name: 'app_admin_category_edit')]
    public function editCategory(Category $category, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Categorie modifiee.');
            return $this->redirectToRoute('app_admin_categories');
        }

        return $this->render('admin/category_form.html.twig', [
            'form' => $form,
            'title' => 'Modifier la categorie',
        ]);
    }

    #[Route('/category/{id}/delete', name: 'app_admin_category_delete', methods: ['POST'])]
    public function deleteCategory(Category $category, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$category->getId(), $request->getPayload()->getString('_token'))) {
            $em->remove($category);
            $em->flush();
            $this->addFlash('success', 'Categorie supprimee.');
        }

        return $this->redirectToRoute('app_admin_categories');
    }

    #[Route('/comments', name: 'app_admin_comments')]
    public function comments(CommentRepository $commentRepository, EntityManagerInterface $em): Response
    {
        $comments = $commentRepository->findBy([], ['createdAt' => 'DESC']);

        $unreadIds = [];
        foreach ($comments as $comment) {
            if (!$comment->isRead()) {
                $unreadIds[] = $comment->getId();
                $comment->setIsRead(true);
            }
        }
        $em->flush();

        return $this->render('admin/comments.html.twig', [
            'comments' => $comments,
            'unreadIds' => $unreadIds,
        ]);
    }

    #[Route('/comment/{id}/approve', name: 'app_admin_comment_approve', methods: ['POST'])]
    public function approveComment(Comment $comment, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('approve'.$comment->getId(), $request->getPayload()->getString('_token'))) {
            $comment->setStatus('valide');
            $em->flush();
            $this->addFlash('success', 'Commentaire approuve.');
        }

        return $this->redirectToRoute('app_admin_comments');
    }

    #[Route('/comment/{id}/reject', name: 'app_admin_comment_reject', methods: ['POST'])]
    public function rejectComment(Comment $comment, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('reject'.$comment->getId(), $request->getPayload()->getString('_token'))) {
            $comment->setStatus('supprime');
            $em->flush();
            $this->addFlash('success', 'Commentaire rejete.');
        }

        return $this->redirectToRoute('app_admin_comments');
    }
}
