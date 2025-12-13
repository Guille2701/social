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
    public function send(User $receiver, EntityManagerInterface $em, FriendInvitationRepository $invitationRepository, \Symfony\Component\HttpFoundation\Request $request): Response
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
            // Si venimos de pending, volvemos allí
            if ($request->query->get('source') === 'pending') {
                 return $this->redirectToRoute('friend_pending');
            }
            return $this->redirectToRoute('profile_view', ['username' => $receiver->getUsername()]);
        }

        $inv = new FriendInvitation();
        $inv->setSender($sender)->setReceiver($receiver);

        $em->persist($inv);
        $em->flush();

        $this->addFlash('success', 'Invitación enviada.');

        // Redirect based on source
        if ($request->query->get('source') === 'pending') {
            return $this->redirectToRoute('friend_pending');
        }

        return $this->redirectToRoute('profile_view', ['username' => $receiver->getUsername()]);
    }

    #[Route('/pending', name: 'friend_pending')]
    public function pending(FriendInvitationRepository $invitationRepo, UserRepository $userRepo): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $invitations = $invitationRepo->findBy(['receiver' => $user, 'status' => 'pending']);
        
        // Usuarios que me siguen pero yo no los sigo (Follow Back)
        // Obtenemos mis seguidores
        $myFollowers = $user->getFollowers();
        // Filtramos aquellos que NO están en mis seguidos
        $followBackList = $myFollowers->filter(function(User $follower) use ($user) {
            return !$user->getFollowing()->contains($follower);
        });

        // Sugerencias (Ahora incluye a los que ya envié invitación pending)
        $suggestions = $userRepo->findSuggestions($user);

        // Obtener lista de usuarios a los que HE ENVIADO solicitud pendiente
        // Para poder mostrar el botón "Solicitud Enviada" / "Cancelar"
        $sentPending = $invitationRepo->findBy(['sender' => $user, 'status' => 'pending']);
        $sentRequests = [];
        foreach ($sentPending as $inv) {
            $sentRequests[] = $inv->getReceiver()->getId();
        }

        return $this->render('friends/pending.html.twig', [
            'invitations' => $invitations,
            'followBackList' => $followBackList,
            'suggestions' => $suggestions,
            'sentRequests' => $sentRequests,
        ]);
    }

    #[Route('/cancel-request/{id}', name: 'friend_cancel_request')]
    public function cancelRequest(User $receiver, EntityManagerInterface $em, FriendInvitationRepository $invitationRepository, \Symfony\Component\HttpFoundation\Request $request): Response
    {
        /** @var User $sender */
        $sender = $this->getUser();

        // Buscar invitación donde YO soy sender, y receiver es $receiver, y status es pending
        $invitation = $invitationRepository->findOneBy([
            'sender' => $sender,
            'receiver' => $receiver,
            'status' => 'pending'
        ]);

        if ($invitation) {
            $em->remove($invitation);
            $em->flush();
            $this->addFlash('success', 'Solicitud de amistad cancelada.');
        }

        return $this->redirectToRoute('friend_pending');
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

        // Al aceptar, el REMITENTE (sender) empieza a seguir al RECEPTOR (currentUser)
        // El receptor NO sigue automáticamente al remitente.
        $sender = $inv->getSender();
        if ($sender) {
            $sender->addFollowing($currentUser);
            // $currentUser->addFollowing($sender); // ELIMINADO: No seguir de vuelta automáticamente
        }

        $em->flush();

        return $this->redirectToRoute('friend_pending');
    }

    #[Route('/follow-back/{id}', name: 'friend_follow_back')]
    public function followBack(User $userToFollow, EntityManagerInterface $em): Response
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        // Verificar que no sea yo mismo
        if ($currentUser === $userToFollow) {
            return $this->redirectToRoute('friend_pending');
        }

        // Verificar si ya lo sigo
        if ($currentUser->getFollowing()->contains($userToFollow)) {
             $this->addFlash('warning', 'Ya sigues a este usuario.');
             return $this->redirectToRoute('friend_pending');
        }

        // Seguir al usuario
        $currentUser->addFollowing($userToFollow);
        $em->flush();

        $this->addFlash('success', 'Ahora sigues a ' . $userToFollow->getUsername());

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
