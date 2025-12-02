<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/chat')]
class ChatController extends AbstractController
{
    #[Route('/{username}', name: 'app_chat_with_user', methods: ['GET', 'POST'])]
    public function chat(
        string $username,
        UserRepository $userRepository,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $userToChat = $userRepository->findOneBy(['username' => $username]);
        $currentUser = $this->getUser();

        if (!$userToChat || !$currentUser) {
            throw $this->createNotFoundException('Usuario no encontrado.');
        }

        // Buscar conversación existente
        $conversation = $em->getRepository(Conversation::class)
            ->findOneBy([
                'user1' => $currentUser,
                'user2' => $userToChat
            ])
            ?? $em->getRepository(Conversation::class)
                ->findOneBy([
                    'user1' => $userToChat,
                    'user2' => $currentUser
                ]);

        // Si no existe → crearla
        if (!$conversation) {
            $conversation = new Conversation();
            $conversation->setUser1($currentUser);
            $conversation->setUser2($userToChat);

            $em->persist($conversation);
            $em->flush();
        }

        // Si se envía mensaje
        if ($request->isMethod('POST')) {
            $msgContent = trim($request->get('message'));

            if ($msgContent !== '') {
                $message = new Message();
                $message->setSender($currentUser);
                $message->setConversation($conversation);
                $message->setContent($msgContent);
                $message->setCreatedAt(new \DateTime());
                $message->setIsRead(false);

                $em->persist($message);
                $em->flush();

                return $this->redirectToRoute('app_chat_with_user', [
                    'username' => $userToChat->getUsername()
                ]);
            }

        }

        return $this->render('chat/chat.html.twig', [
            'userToChat' => $userToChat,
            'messages' => $conversation->getMessages()
        ]);
    }
}
