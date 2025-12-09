<?php

namespace App\Controller;

use App\Entity\Friend;
use App\Entity\User;
use App\Repository\FriendRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


class FriendController extends AbstractController
{
    #[Route('/friends', name: 'friends_list')]
    public function list(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        return $this->render('friends/list.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/friends/invite/{id}', name: 'friends_invite')]
    public function invite(User $receiver, EntityManagerInterface $entityManager): Response
    {
        $sender = $this->getUser();

        if (!$sender instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $friend = new Friend();
        $friend->setSender($sender);
        $friend->setReceiver($receiver);
        $friend->setStatus('pending');

        $entityManager->persist($friend);
        $entityManager->flush();

        return $this->redirectToRoute('friends_list');
    }

    #[Route('/friends/accept/{id}', name: 'friends_accept')]
    public function accept(Friend $friend, EntityManagerInterface $entityManager): Response
    {
        $friendship->setStatus('accepted');
        $entityManager->flush();

        return $this->redirectToRoute('friends_list');
    }

    #[Route('/friends/reject/{id}', name: 'friends_reject')]
    public function reject(Friendship $friendship, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($friendship);
        $entityManager->flush();

        return $this->redirectToRoute('friends_list');
    }
}