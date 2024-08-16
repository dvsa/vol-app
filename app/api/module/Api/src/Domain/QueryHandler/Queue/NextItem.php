<?php

/**
 * Next Item Queue Query Handler
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Queue;

use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Doctrine\ORM\OptimisticLockException;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Domain\Repository\Queue as QueueRepo;
use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Api\Domain\Query\Queue\NextItem as NextItemQuery;
use Olcs\Logging\Log\Logger;

/**
 * Next Item Queue Query Handler
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class NextItem extends AbstractQueryHandler
{
    protected $repoServiceName = 'Queue';

    /**
     * Handle query
     *
     * @param QueryInterface $query Query
     *
     * @return QueueEntity|null
     */
    public function handleQuery(QueryInterface $query)
    {
        /**
         * @var QueueRepo $repo
         * @var QueueEntity $entity
         * @var NextItemQuery $query
         */
        $repo = $this->getRepo();

        try {
            $entity = $repo->getNextItem($query->getIncludeTypes(), $query->getExcludeTypes());
        } catch (NotFoundException) {
            return null;
        } catch (OptimisticLockException) {
            Logger::info('skipping - another queue process is already working on this record');
            return null;
        }

        return $entity;
    }
}
