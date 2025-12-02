<?php

namespace App\Controller;

use App\Entity\FriendInvitation;
use App\Entity\User;
use App\Repository\FriendInvitationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/friends')]
#[IsGranted('ROLE_USER')]
class FriendInvitationController extends AbstractController
{
    #[Route('/', name: 'friend_index', methods: ['GET'])]
    public function index(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        // Amigos son aquellos a los que sigues y te siguen (mutuo)
        $friends = $user->getFollowing()->filter(
            fn(User $following) => $user->getFollowers()->contains($following)
        );

        return $this->render('friends/index.html.twig', [
            'friends' => $friends,
        ]);
    }

    #[Route('/send/{id}', name: 'friend_send')]
    public function send(User $receiver, EntityManagerInterface $em, FriendInvitationRepository $invitationRepository): Response
    {
        /** @var User $sender */
        $sender = $this->getUser();

        if ($receiver === $sender) {
            $this->addFlash('error', 'No puedes enviarte una invitación a ti mismo.');
            return $this->redirectToRoute('profile_view', ['username' => $receiver->getUsername()]);
        }

        // Comprobar si ya existe una invitación pendiente
        if ($invitationRepository->findExistingInvitation($sender, $receiver)) {
            $this->addFlash('warning', 'Ya existe una invitación de amistad pendiente con este usuario.');
            return $this->redirectToRoute('profile_view', ['username' => $receiver->getUsername()]);
        }

        $inv = new FriendInvitation();
        $inv->setSender($sender)->setReceiver($receiver);

        $em->persist($inv);
        $em->flush();

        $this->addFlash('success', 'Invitación enviada.');
        return $this->redirectToRoute('profile_view', ['username' => $receiver->getUsername()]);
    }

    #[Route('/pending', name: 'friend_pending')]
    public function pending(FriendInvitationRepository $invitationRepo, UserRepository $userRepo): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $invitations = $invitationRepo->findBy(['receiver' => $user, 'status' => 'pending']);
        $suggestions = $userRepo->findSuggestions($user);

        return $this->render('friends/pending.html.twig', [
            'invitations' => $invitations,
            'suggestions' => $suggestions,
        ]);
    }

    #[Route('/accept/{id}', name: 'friend_accept')]
    public function accept(FriendInvitation $inv, EntityManagerInterface $em): Response
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        if ($inv->getReceiver() !== $currentUser) {
            throw $this->createAccessDeniedException();
        }

        $inv->setStatus('accepted');

        // Al aceptar, ambos usuarios se siguen mutuamente
        $sender = $inv->getSender();
        if ($sender) {
            $currentUser->addFollowing($sender);
            $sender->addFollowing($currentUser);
        }

        $em->flush();

        return $this->redirectToRoute('friend_pending');
    }

    #[Route('/reject/{id}', name: 'friend_reject')]
    public function reject(FriendInvitation $inv, EntityManagerInterface $em): Response
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        if ($inv->getReceiver() !== $currentUser) {
            throw $this->createAccessDeniedException();
        }

        $inv->setStatus('rejected');
        $em->flush();

        return $this->redirectToRoute('friend_pending');
    }
}
