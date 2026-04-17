<?php

/**
 * Abstract Operating Centres Review Service
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Dvsa\Olcs\Api\Entity\System\RefData;

/**
 * Abstract Operating Centres Review Service
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
abstract class AbstractOperatingCentresReviewService extends AbstractReviewService
{
    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getHeaderTranslationKey(array $reviewData, $section)
    {
        if ($reviewData['vehicleType']['id'] == RefData::APP_VEHICLE_TYPE_LGV) {
            return 'review-authorisation';
        }

        return parent::getHeaderTranslationKey($reviewData, $section);
    }
}
