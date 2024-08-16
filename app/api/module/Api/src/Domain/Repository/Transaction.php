<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Exception;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;
use Dvsa\Olcs\Api\Entity\Fee\Transaction as Entity;

/**
 * Transaction
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class Transaction extends AbstractRepository
{
    protected $entity = Entity::class;

    protected $alias = 't';

    /**
     * Fetch data by reference
     *
     * @param string $reference   Reference Nu
     * @param int    $hydrateMode Hydration mode
     * @param int|null   $version     Version Nr
     *
     * @return Entity
     */
    public function fetchByReference($reference, $hydrateMode = Query::HYDRATE_OBJECT, $version = null)
    {
        $qb = $this->createQueryBuilder();

        $this->getQueryBuilder()
            ->modifyQuery($qb)
            ->withRefdata()
            ->with('feeTransactions', 'ft')
            ->with('ft.fee', 'f')
            ->with('f.licence', 'l')
            ->with('f.application')
            ->with('l.organisation');

        $qb
            ->andWhere($qb->expr()->eq($this->alias . '.reference', ':reference'))
            ->setParameter('reference', $reference);

        $results = $qb->getQuery()->getResult($hydrateMode);

        if (empty($results)) {
            throw new Exception\NotFoundException('Resource not found');
        }

        if ($hydrateMode === Query::HYDRATE_OBJECT && $version !== null) {
            $this->lock($results[0], $version);
        }

        return $results[0];
    }

    /**
     * Fetch outstanding card payments
     *
     * @param int $minAge minimum age in minutes
     *
     * @return array
     */
    public function fetchOutstandingCardPayments($minAge)
    {
        $qb = $this->createQueryBuilder();

        $qb
            ->andWhere($qb->expr()->eq($this->alias . '.type', ':transactionType'))
            ->andWhere($qb->expr()->eq($this->alias . '.status', ':status'))
            ->andWhere($qb->expr()->in($this->alias . '.paymentMethod', ':paymentMethods'))
            ->setParameter('transactionType', $this->getRefdataReference(Entity::TYPE_PAYMENT))
            ->setParameter('status', $this->getRefdataReference(Entity::STATUS_OUTSTANDING))
            ->setParameter(
                'paymentMethods',
                [
                    FeeEntity::METHOD_CARD_ONLINE,
                    FeeEntity::METHOD_CARD_OFFLINE,
                ]
            );

        if ($minAge) {
            $maxCreatedOn = new DateTime();
            $maxCreatedOn->sub(new \DateInterval(sprintf('PT%dM', $minAge)));
            $qb
                ->andWhere($qb->expr()->lt($this->alias . '.createdOn', ':maxCreatedOn'))
                ->setParameter('maxCreatedOn', $maxCreatedOn);
        }

        return $qb->getQuery()->getResult();
    }
}
