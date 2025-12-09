<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/user')]
final class UserController extends AbstractController
{
    #[Route(name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    /*
    #[Route('/{id}', name: 'app_user_makeAdmin', methods: ['GET'])]
    public function makeAdmin(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }*/

    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{username}/conversations', name: 'app_user_conversations', methods: ['GET'])]
    public function conversations(UserRepository $userRepository, string $username): Response
    {
        $user = $userRepository->findOneBy(['username' => $username]);

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        return $this->render('user/conversations.html.twig', [
            'user' => $user,
            'conversations' => $user->getConversations(), // 👈 ESTA LÍNEA ES LA CLAVE
        ]);
    }

    #[Route('/follow/{id}', name: 'app_user_follow', methods: ['GET'])]
    public function follow(User $userToFollow, EntityManagerInterface $em): Response
    {
        $currentUser = $this->getUser();

        if (!$currentUser) {
            return $this->redirectToRoute('app_login');
        }

        if ($currentUser === $userToFollow) {
            $this->addFlash('error', 'No puedes seguirte a ti mismo.');
            return $this->redirectToRoute('app_profile', ['id' => $userToFollow->getId()]);
        }

        // Comprobar si ya sigue al usuario
        $alreadyFollowing = $userToFollow->getFollowers()->contains($currentUser);

        if ($alreadyFollowing) {
            // Unfollow: lo removemos de followers del usuario objetivo
            $userToFollow->removeFollower($currentUser);
        } else {
            // Follow: lo agregamos a followers del usuario objetivo
            $userToFollow->addFollower($currentUser);
        }

        $em->flush();

        return $this->redirectToRoute('app_post_index', ['id' => $userToFollow->getId()]);
    }


}
