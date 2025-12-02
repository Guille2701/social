<?php
namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
final class AdminController extends AbstractController
{
    #[Route(name: 'app_admin_dashboard', methods: ['GET'])]
    public function dashboard(UserRepository $userRepository, PostRepository $postRepository): Response
    {
        return $this->render('admin/dashboard.html.twig', [
            'users' => $userRepository->findAll(),
            'posts' => $postRepository->findAll(),
            'totalUsers' => count($userRepository->findAll()),
            'totalPosts' => count($postRepository->findAll()),
        ]);
    }

    #[Route('/user/{id}/make-admin', name: 'app_admin_make_admin', methods: ['POST'])]
    public function makeAdmin(User $user, Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('make_admin' . $user->getId(), (string)$request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF inválido.');
            return $this->redirectToRoute('app_admin_dashboard');
        }

        $user->setAdmin(true);
        $em->flush();

        $this->addFlash('success', 'Usuario promovido a administrador.');
        return $this->redirectToRoute('app_admin_dashboard');
    }

    #[Route('/user/{id}/remove-admin', name: 'app_admin_remove_admin', methods: ['POST'])]
    public function removeAdmin(User $user, Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('remove_admin' . $user->getId(), (string)$request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF inválido.');
            return $this->redirectToRoute('app_admin_dashboard');
        }

        // Evitar que un admin se quite a sí mismo el rol por accidente, opcional
        // if ($this->getUser() && $this->getUser()->getId() === $user->getId()) {
        //     $this->addFlash('error', 'No puedes quitarte a ti mismo el rol de admin.');
        //     return $this->redirectToRoute('app_admin_dashboard');
        // }

        $user->setAdmin(false);
        $em->flush();

        $this->addFlash('success', 'Rol de administrador eliminado.');
        return $this->redirectToRoute('app_admin_dashboard');
    }
}
