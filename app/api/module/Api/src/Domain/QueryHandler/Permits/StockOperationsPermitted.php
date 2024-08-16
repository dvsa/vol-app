<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Permits;

use Dvsa\Olcs\Transfer\Query\Permits\StockOperationsPermitted as StockOperationsPermittedQuery;
use Dvsa\Olcs\Api\Domain\Query\Permits\QueueRunScoringPermitted as QueueRunScoringPermittedQuery;
use Dvsa\Olcs\Api\Domain\Query\Permits\QueueAcceptScoringAndPostScoringReportPermitted
    as QueueAcceptScoringAndPostScoringReportPermittedQuery;
use Dvsa\Olcs\Api\Domain\Query\Permits\DeviationData as DeviationDataQuery;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Stock operations permitted
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class StockOperationsPermitted extends AbstractQueryHandler
{
    protected $repoServiceName = 'IrhpPermitStock';

    protected $extraRepos = ['IrhpApplication'];

    /**
     * Handle query
     *
     * @param QueryInterface|StockOperationsPermittedQuery $query query
     *
     * @return array
     */
    public function handleQuery(QueryInterface $query)
    {
        $stockId = $query->getId();

        $stock = $this->getRepo()->fetchById($stockId);
        $stockStatus = $stock->getStatus();
        $stockIdParams = ['id' => $stockId];

        $queryHandler = $this->getQueryHandler();
        $scoringResult = $queryHandler->handleQuery(QueueRunScoringPermittedQuery::create($stockIdParams));
        $acceptAndPostScoringReportResult = $queryHandler->handleQuery(
            QueueAcceptScoringAndPostScoringReportPermittedQuery::create($stockIdParams)
        );

        $sourceValues = $this->getRepo('IrhpApplication')->fetchDeviationSourceValues($stockId);
        $deviationData = $this->getQueryHandler()->handleQuery(
            DeviationDataQuery::create(['sourceValues' => $sourceValues])
        );

        return [
            'stockStatusId' => $stockStatus->getId(),
            'stockStatusMessage' => $stockStatus->getDescription(),
            'scoringPermitted' => $scoringResult['result'],
            'scoringMessage' => $scoringResult['message'],
            'acceptAndPostScoringReportPermitted' => $acceptAndPostScoringReportResult['result'],
            'acceptAndPostScoringReportMessage' => $acceptAndPostScoringReportResult['message'],
            'meanDeviation' => $deviationData['meanDeviation']
        ];
    }
}
