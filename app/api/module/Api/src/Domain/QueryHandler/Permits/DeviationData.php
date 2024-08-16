<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Permits;

use Dvsa\Olcs\Api\Domain\Query\Permits\DeviationData as DeviationDataQuery;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Deviation data
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class DeviationData extends AbstractQueryHandler
{
    /**
     * Handle query
     *
     * @param QueryInterface|DeviationDataQuery $query query
     *
     * @return array
     */
    public function handleQuery(QueryInterface $query)
    {
        $sourceValues = $query->getSourceValues();

        $licences = [];
        foreach ($sourceValues as $sourceValue) {
            $licNo = $sourceValue['licNo'];
            $applicationId = $sourceValue['applicationId'];
            $permitsRequired = $sourceValue['permitsRequired'];

            $licences[$licNo][$applicationId] = $permitsRequired;
        }

        $meanDeviation = null;
        if (count($licences) > 0) {
            $meanDeviation = count($sourceValues) / count($licences);
        }

        return [
            'licenceData' => $licences,
            'meanDeviation' => $meanDeviation,
        ];
    }
}
