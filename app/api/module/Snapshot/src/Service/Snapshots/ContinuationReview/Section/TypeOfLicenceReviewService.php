<?php

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ContinuationReview\Section;

use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail;
use Dvsa\Olcs\Api\Entity\Licence\Licence;

/**
 * Type Of Licence Continuation Review Service
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TypeOfLicenceReviewService extends AbstractReviewService
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

        $config = [
            [
                ['value' => 'continuation-review-operator-location'],
                ['value' => $licence->getOperatorLocation(), 'header' => true]
            ]
        ];

        if ($licence->getNiFlag() !== 'Y') {
            $config[] = [
                ['value' => 'continuation-review-operator-type'],
                ['value' => $licence->getOperatorType(), 'header' => true]
            ];
        }

        $config[] = [
            ['value' => 'continuation-review-licence-type'],
            ['value' => $licence->getLicenceType()->getDescription(), 'header' => true]
        ];

        return $config;
    }
}
