<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Letter\LetterInstance as Entity;

/**
 * LetterInstance Repository
 */
class LetterInstance extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * The letter a generated document was built from, if it came from the letter builder
     */
    public function fetchByDocument(int $documentId): ?Entity
    {
        return $this->getRepository()->findOneBy(['document' => $documentId]);
    }

    /**
     * The letters of one type most recently sent that were raised on a given licence,
     * application or anything else a letter can be raised on, newest first
     *
     * @param string $raisedOn the letter's link to it, one of Entity::RAISED_ON
     * @return list<Entity>
     */
    public function fetchLastSent(int $letterTypeId, string $raisedOn, int $id, int $howMany): array
    {
        // $raisedOn goes into the query as it is, so it has to be one of the letter's own links
        $position = array_search($raisedOn, Entity::RAISED_ON, true);
        if ($position === false) {
            throw new \InvalidArgumentException('A letter cannot be raised on ' . $raisedOn);
        }

        $qb = $this->createQueryBuilder();
        $qb->andWhere($qb->expr()->eq($this->alias . '.letterType', ':letterType'))
            ->andWhere($qb->expr()->eq($this->alias . '.' . $raisedOn, ':raisedOn'));

        // Raised on it, not just linked to it: every letter raised on an application carries
        // the licence too, and those aren't the licence's own letters
        foreach (array_slice(Entity::RAISED_ON, 0, $position) as $moreSpecific) {
            $qb->andWhere($qb->expr()->isNull($this->alias . '.' . $moreSpecific));
        }

        // The choices and issues come back with the letters, so listing them doesn't read each
        // letter's one at a time. The paginator keeps the limit on letters, not joined rows.
        $qb->andWhere($qb->expr()->eq($this->alias . '.status', ':sent'))
            ->addSelect('c', 'lc', 'i', 'v')
            ->leftJoin($this->alias . '.letterInstanceChoices', 'c')
            ->leftJoin('c.letterChoice', 'lc')
            ->leftJoin($this->alias . '.letterInstanceIssues', 'i')
            ->leftJoin('i.letterIssueVersion', 'v')
            ->setParameter('letterType', $letterTypeId)
            ->setParameter('raisedOn', $id)
            ->setParameter('sent', Entity::STATUS_SENT)
            ->orderBy($this->alias . '.sentOn', \SortDirection::Descending)
            ->addOrderBy($this->alias . '.id', \SortDirection::Descending)
            ->setMaxResults($howMany);

        return iterator_to_array($this->getPaginator($qb->getQuery()), false);
    }
}
