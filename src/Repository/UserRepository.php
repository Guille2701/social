<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

        public function findSuggestions(User $user, int $limit = 5): array
    {
        $em = $this->getEntityManager();

        // Paso 1: Obtener todos los IDs a excluir en un solo array
        $followingIds = $em->createQuery('SELECT f.id FROM App\Entity\User u JOIN u.following f WHERE u.id = :user_id')
            ->setParameter('user_id', $user->getId())
            ->getSingleColumnResult();

        // Excluir amigos (accepted) o solicitudes pendientes recibidas (pending AND receiver = user)
        // PERMITIR (no excluir): solicitudes pendientes enviadas (sender = user AND pending) -> Para mostrar "Solicitud enviada"
        $invitationIds = $em->createQuery("
            SELECT CASE WHEN fi.sender = :user_id THEN IDENTITY(fi.receiver) ELSE IDENTITY(fi.sender) END
            FROM App\Entity\FriendInvitation fi
            WHERE 
                (fi.status = 'accepted' AND (fi.sender = :user_id OR fi.receiver = :user_id))
                OR
                (fi.status = 'pending' AND fi.receiver = :user_id)
        ")->setParameter('user_id', $user->getId())->getSingleColumnResult();

        // Unir todos los IDs a excluir, incluyendo el del propio usuario
        $excludeIds = array_merge($followingIds, $invitationIds, [$user->getId()]);

        $qb = $this->createQueryBuilder('u');
        if (!empty($excludeIds)) {
            $qb->where($qb->expr()->notIn('u.id', ':excludeIds'))
               ->setParameter('excludeIds', $excludeIds);
        }
        $qb
            ->setMaxResults($limit)
            ->orderBy('u.id', 'DESC'); // Ordenar para obtener usuarios más nuevos

        return $qb->getQuery()->getResult();
    }



//    /**
//     * @return User[] Returns an array of User objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?User
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
