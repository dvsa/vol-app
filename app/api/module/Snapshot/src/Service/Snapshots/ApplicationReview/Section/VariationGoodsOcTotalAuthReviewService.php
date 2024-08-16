<?php

/**
 * Variation Goods Oc Total Auth Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Dvsa\Olcs\Api\Entity\Licence\Licence;

/**
 * Variation Goods Oc Total Auth Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationGoodsOcTotalAuthReviewService extends AbstractVariationOcTotalAuthReviewService
{
    /**
     * Get the keys of the values to compare
     *
     * @param array $data
     * @return string
     */
    protected function getChangedKeys($data)
    {
        $changedKeys = [];

        if (!is_null($data['totAuthLgvVehicles'])) {
            $changedKeys['totAuthHgvVehicles'] = 'vehicles-hgv';
            $changedKeys['totAuthLgvVehicles'] = 'vehicles-lgv';
        } else {
            $changedKeys['totAuthVehicles'] = 'vehicles';
        }

        $changedKeys['totAuthTrailers'] = 'trailers';

        if ($data['licenceType']['id'] === Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL) {
            $changedKeys['totCommunityLicences'] = 'community-licences';
        }

        return $changedKeys;
    }
}
