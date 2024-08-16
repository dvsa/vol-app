<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Organisation\CorrespondenceInbox as Entity;

/**
 * Correspondence Inbox
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CorrespondenceInbox extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * Get All Requiring Print
     *
     * @param string $minDate Date From
     * @param string $maxDate Date To
     *
     * @return array
     */
    public function getAllRequiringPrint($minDate, $maxDate)
    {
        $qb = $this->createQueryBuilder();

        $qb->addSelect('d, l')
            ->join($this->alias . '.document', 'd')
            ->join($this->alias . '.licence', 'l');

        $this->getQueryBuilder()
            ->modifyQuery($qb);

        $qb->andWhere($qb->expr()->eq('l.translateToWelsh', 0));
        $qb->andWhere($qb->expr()->eq($this->alias . '.accessed', 0));
        $qb->andWhere($qb->expr()->gte($this->alias . '.createdOn', ':minDate'));
        $qb->andWhere($qb->expr()->lte($this->alias . '.createdOn', ':maxDate'));
        // print queries don't care about the emailReminderSent flag;
        // whether a reminder has or hasn't been sent doesn't affect
        // whether it needs printing or not
        $qb->andWhere($qb->expr()->eq($this->alias . '.printed', 0));
        $qb->andWhere($qb->expr()->isNotNull('l.id'));

        $qb->setParameter('minDate', $minDate);
        $qb->setParameter('maxDate', $maxDate);

        return $qb->getQuery()
            ->setFetchMode(Entity::class, 'document', \Doctrine\ORM\Mapping\ClassMetadata::FETCH_EAGER)
            ->getResult();
    }

    /**
     * Get All Requiring Reminder
     *
     * @param string $minDate Date From
     * @param string $maxDate Date To
     *
     * @return array
     */
    public function getAllRequiringReminder($minDate, $maxDate)
    {
        $qb = $this->createQueryBuilder();

        $qb->addSelect('d, l, lo, lou, louu, louucd')
            ->join($this->alias . '.document', 'd')
            ->join($this->alias . '.licence', 'l')
            ->join('l.organisation', 'lo')
            ->join('lo.organisationUsers', 'lou')
            ->join('lou.user', 'louu')
            ->join('louu.contactDetails', 'louucd');

        $this->getQueryBuilder()
            ->modifyQuery($qb)
            ->with('d.continuationDetails', 'cd')
            ->with('cd.checklistDocument', 'cdd');

        $qb->andWhere($qb->expr()->eq($this->alias . '.accessed', 0));
        $qb->andWhere($qb->expr()->gte($this->alias . '.createdOn', ':minDate'));
        $qb->andWhere($qb->expr()->lte($this->alias . '.createdOn', ':maxDate'));
        // don't fetch ones we've already sent...
        $qb->andWhere($qb->expr()->eq($this->alias . '.emailReminderSent', 0));
        // ... but also ignore ones we may have printed but
        // *not* sent reminders for - e.g. if org has no email
        // addresses (somehow) - without this check we'd continually
        // try and email the reminder long after the print threshold
        // had been reached
        $qb->andWhere($qb->expr()->eq($this->alias . '.printed', 0));
        $qb->andWhere($qb->expr()->isNotNull('l.id'));
        $qb->andWhere($qb->expr()->eq('l.translateToWelsh', 0));

        $qb->setParameter('minDate', $minDate);
        $qb->setParameter('maxDate', $maxDate);

        return $qb->getQuery()
            ->setFetchMode(Entity::class, 'document', \Doctrine\ORM\Mapping\ClassMetadata::FETCH_EAGER)
            ->getResult();
    }

    /**
     * Fetch by document id
     *
     * @param int $documentId document id
     *
     * @return array
     */
    public function fetchByDocumentId($documentId)
    {
        $qb = $this->createQueryBuilder();
        $qb->andWhere($qb->expr()->eq($this->alias . '.document', ':document'));
        $qb->setParameter('document', $documentId);

        return $qb->getQuery()->getResult();
    }
}
