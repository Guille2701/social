<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\User;
use App\Repository\ConversationRepository;
use App\Entity\Message;
use App\Form\MessageType;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/conversation')]
class ConversationController extends AbstractController
{
     #[Route(name: 'app_conversation_index', methods: ['GET'])]
    public function index(ConversationRepository $conversationRepository): Response
    {
        $user = $this->getUser();
        $conversations = $conversationRepository->findByUser($user);

        return $this->render('conversation/index.html.twig', [
            'conversations' => $conversations,
        ]);
    }

    #[Route('/start/{id}', name: 'conversation_start')]
    public function start(
        User $otherUser,
        ConversationRepository $conversationRepository,
        EntityManagerInterface $em
    ): Response {
        $currentUser = $this->getUser();

        // No chatear conmigo mismo
        if ($currentUser === $otherUser) {
            $this->addFlash('error', 'No puedes chatear contigo mismo.');
            return $this->redirectToRoute('app_conversation_index');
        }

        // Buscar si ya existe conversación
        $conversation = $conversationRepository->findOneByUsers($currentUser, $otherUser);

        if ($conversation) {
            return $this->redirectToRoute('app_conversation_show', [
                'id' => $conversation->getId()
            ]);
        }

        // Crear nueva conversación
        $conversation = new Conversation();
        $conversation->setUser1($currentUser);
        $conversation->setUser2($otherUser);

        $em->persist($conversation);
        $em->flush();

        return $this->redirectToRoute('app_conversation_show', [
            'id' => $conversation->getId()
        ]);
    }

    #[Route('/{id}', name: 'app_conversation_show', methods: ['GET', 'POST'])]
public function show(
    Request $request,
    Conversation $conversation,
    MessageRepository $messageRepository,
    EntityManagerInterface $em
): Response {
    $currentUser = $this->getUser();

    // Seguridad
    if ($conversation->getUser1() !== $currentUser && $conversation->getUser2() !== $currentUser) {
        throw $this->createAccessDeniedException();
    }

    // Form de mensaje
    $message = new Message();
    $form = $this->createForm(MessageType::class, $message);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $message->setConversation($conversation);
        $message->setSender($currentUser);
        $em->persist($message);
        $em->flush();

        return $this->redirectToRoute('app_conversation_show', ['id' => $conversation->getId()]);
    }

    $messages = $messageRepository->findBy(
        ['conversation' => $conversation],
        ['createdAt' => 'ASC']
    );

    return $this->render('conversation/show.html.twig', [
        'conversation' => $conversation,
        'messages' => $messages,
        'form' => $form->createView(),
    ]);
}

}
