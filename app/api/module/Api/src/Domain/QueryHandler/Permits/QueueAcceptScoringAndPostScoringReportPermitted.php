<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Permits;

use Dvsa\Olcs\Api\Domain\Query\Permits\QueueAcceptScoringAndPostScoringReportPermitted
    as QueueAcceptScoringAndPostScoringReportPermittedQuery;
use Dvsa\Olcs\Api\Domain\Query\Permits\CheckAcceptScoringAndPostScoringReportPrerequisites;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Queue accept scoring and post scoring report permitted
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class QueueAcceptScoringAndPostScoringReportPermitted extends AbstractQueryHandler
{
    protected $repoServiceName = 'IrhpPermitStock';

    /**
     * Handle query
     *
     * @param QueryInterface|QueueAcceptScoringAndPostScoringReportPermittedQuery $query query
     *
     * @return array
     */
    public function handleQuery(QueryInterface $query)
    {
        $stockId = $query->getId();
        $stock = $this->getRepo()->fetchById($stockId);

        if (!$stock->statusAllowsQueueAcceptScoringAndPostScoringReport()) {
            return [
                'result' => false,
                'message' => sprintf(
                    'Acceptance, stock alignment and post scoring report are not permitted when stock status is \'%s\'',
                    $stock->getStatusDescription()
                )
            ];
        }

        return $this->getQueryHandler()->handleQuery(
            CheckAcceptScoringAndPostScoringReportPrerequisites::create(['id' => $stockId])
        );
    }
}
