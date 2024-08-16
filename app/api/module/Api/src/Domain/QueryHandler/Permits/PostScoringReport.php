<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Permits;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\Permits\PostScoringReport as PostScoringReportQuery;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Post scoring report
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class PostScoringReport extends AbstractQueryHandler
{
    protected $repoServiceName = 'IrhpApplication';

    /**
     * Handle query
     *
     * @param QueryInterface|PostScoringReportQuery $query query
     *
     * @return array
     */
    public function handleQuery(QueryInterface $query)
    {
        $stockId = $query->getId();

        $applicationIds = $this->getRepo()->fetchInScopeUnderConsiderationApplicationIds($stockId);

        foreach ($applicationIds as $applicationId) {
            $application = $this->getRepo()->fetchById($applicationId);
            if ($application->hasStateRequiredForPostScoringEmail()) {
                $rows[] = [$applicationId];
            }
        }

        return ['rows' => $rows];
    }
}
