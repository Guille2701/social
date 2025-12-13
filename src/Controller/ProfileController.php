<?php

namespace App\Controller;

use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/profile')]
#[IsGranted('ROLE_USER')]
class ProfileController extends AbstractController
{
    #[Route('/{username}', name: 'profile_view', methods: ['GET'])]
    public function view(#[MapEntity(mapping: ['username' => 'username'])] User $user, \App\Repository\FriendInvitationRepository $invitationRepo): Response
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $isInvitationPending = false;

        if ($currentUser) {
            $invitation = $invitationRepo->findExistingInvitation($currentUser, $user);
            // Check if I sent the invitation AND it is pending (the repo method already filters by pending)
            // But we need to make sure *I* am the sender for "Solicitud Enviada".
            // Actually repo checks both directions.
            // If I am sender -> "Solicitud Enviada"
            // If I am receiver -> "Aceptar Solicitud" (maybe? but user asked for "Request Sent" logic specifically)
            
            if ($invitation && $invitation->getSender() === $currentUser) {
                $isInvitationPending = true;
            }
        }

        return $this->render('profile/view.html.twig', [
            'user' => $user,
            'isInvitationPending' => $isInvitationPending,
        ]);
    }
}