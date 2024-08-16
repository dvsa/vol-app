<?php

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ContinuationReview\Section;

use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail;
use Dvsa\Olcs\Api\Entity\Licence\Licence;

/**
 * Declaration Continuation Review Service
 */
class ConditionsUndertakingsReviewService extends AbstractReviewService
{
    /**
     * Format the readonly config from the given data
     *
     * @param ContinuationDetail $continuationDetail continuation detail
     *
     * @return array
     */
    public function getConfigFromData(ContinuationDetail $continuationDetail)
    {
        /** @var Licence $licence */
        $licence = $continuationDetail->getLicence();

        return [
            'mainItems' => [
                [
                    'partial' => 'continuation-conditions-undertakings',
                    'variables' => [
                        'isPsvRestricted' => $this->isPsvRestricted($licence),
                        'conditionsUndertakings' => $licence->getGroupedConditionsUndertakings()
                    ]
                ],
            ]
        ];
    }

    protected function isPsvRestricted(Licence $licence)
    {
        return $licence->isPsv() && $licence->isRestricted();
    }
}
