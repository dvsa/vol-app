<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Permits;

use Dvsa\Olcs\Api\Domain\Query\Permits\QueueRunScoringPermitted as QueueRunScoringPermittedQuery;
use Dvsa\Olcs\Api\Domain\Query\Permits\CheckRunScoringPrerequisites;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Queue run scoring permitted
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class QueueRunScoringPermitted extends AbstractQueryHandler
{
    protected $repoServiceName = 'IrhpPermitStock';

    /**
     * Handle query
     *
     * @param QueryInterface|QueueRunScoringPermittedQuery $query query
     *
     * @return array
     */
    public function handleQuery(QueryInterface $query)
    {
        $stockId = $query->getId();
        $stock = $this->getRepo()->fetchById($stockId);

        if (!$stock->statusAllowsQueueRunScoring()) {
            return [
                'result' => false,
                'message' => sprintf(
                    'Scoring is not permitted when stock status is \'%s\'',
                    $stock->getStatusDescription()
                )
            ];
        }

        return $this->getQueryHandler()->handleQuery(
            CheckRunScoringPrerequisites::create(['id' => $stockId])
        );
    }
}
