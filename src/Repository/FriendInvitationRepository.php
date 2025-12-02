<?php

namespace App\Repository;

use App\Entity\FriendInvitation;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class FriendInvitationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FriendInvitation::class);
    }

    public function findExistingInvitation(User $sender, User $receiver): ?FriendInvitation
    {
        return $this->createQueryBuilder('fi')
            ->where('((fi.sender = :sender AND fi.receiver = :receiver) OR (fi.sender = :receiver AND fi.receiver = :sender))')
            ->andWhere("fi.status = 'pending'") // Solo nos interesan las pendientes
            ->setParameter('sender', $sender)
            ->setParameter('receiver', $receiver)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
