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
    public function view(#[MapEntity(mapping: ['username' => 'username'])] User $user): Response
    {
        return $this->render('profile/view.html.twig', [
            'user' => $user,
        ]);
    }
}