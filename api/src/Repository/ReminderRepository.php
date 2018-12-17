<?php

namespace App\Repository;

use App\Entity\Reminder;
use Carbon\Carbon;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ReminderRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Reminder::class);
    }

    /**
     * @return Reminder[]
     */
    public function getPastDue()
    {
        $today = Carbon::createMidnightDate();
        $qb = $this->createQueryBuilder('r');
        $qb->andWhere(
          $qb->expr()->lte('r.startDate', ':today')
        );
        $qb->andWhere(
          $qb->expr()->isNull('r.parentReminder')
        );
        $qb->andWhere(
          $qb->expr()->eq('r.status', ':status')
        );
        $qb->setParameter(':status', 'scheduled');
        $qb->setParameter(':today', $today->format('Y-m-d'));

        return $qb->getQuery()->getResult();
    }

    /**
     * Gets all reminders that occur after the specified date.
     *
     * @param \DateTime $after
     *
     * @return Reminder[]
     */
    public function getAfter(\DateTime $after)
    {
        $after->setTime(0, 0, 0);

        $qb = $this->createQueryBuilder('r');
        $qb->andWhere(
          $qb->expr()->gte('r.startDate', ':after')
        );
        $qb->orWhere(
          $qb->expr()->eq('r.status', ':status')
        );
        $qb->setParameter(':after', $after->format('Y-m-d'));
        $qb->setParameter(':status', 'past due');

        return $qb->getQuery()->getResult();
    }
}