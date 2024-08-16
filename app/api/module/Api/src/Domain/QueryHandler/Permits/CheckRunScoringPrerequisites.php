<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Permits;

use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Query\Permits\CheckRunScoringPrerequisites as CheckRunScoringPrerequisitesQuery;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Check run scoring prerequisites
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class CheckRunScoringPrerequisites extends AbstractQueryHandler
{
    protected $repoServiceName = 'IrhpPermitRange';

    protected $extraRepos = ['IrhpPermitWindow', 'IrhpPermit', 'IrhpApplication'];

    /**
     * Handle query
     *
     * @param QueryInterface|CheckRunScoringPrerequisitesQuery $query query
     *
     * @return array
     */
    public function handleQuery(QueryInterface $query)
    {
        $stockId = $query->getId();

        $windowOpen = true;
        try {
            $this->getRepo('IrhpPermitWindow')->fetchLastOpenWindowByStockId($stockId);
        } catch (NotFoundException) {
            $windowOpen = false;
        }

        if ($windowOpen) {
            return $this->generateResponse(
                false,
                'A window is currently open within the stock'
            );
        }

        $applicationIds = $this->getRepo('IrhpApplication')->fetchApplicationIdsAwaitingScoring($stockId);
        if (count($applicationIds) == 0) {
            return $this->generateResponse(
                false,
                'No under consideration applications available'
            );
        }

        $combinedRangeSize = $this->getRepo()->getCombinedRangeSize($stockId);
        if (is_null($combinedRangeSize)) {
            return $this->generateResponse(
                false,
                'No ranges available in this stock'
            );
        }

        $assignedPermits = $this->getRepo('IrhpPermit')->getPermitCount($stockId);
        $permitsAvailable = $combinedRangeSize - $assignedPermits;
        if ($permitsAvailable < 1) {
            return $this->generateResponse(
                false,
                'No free permits available within the stock'
            );
        }

        return $this->generateResponse(true, 'Prerequisites passed');
    }

    /**
     * Generate an array representing the query response
     *
     * @param bool $permitted
     * @param string $message
     *
     * @return array
     */
    private function generateResponse($permitted, $message)
    {
        return [
            'result' => $permitted,
            'message' => $message
        ];
    }
}
