<?php

/**
 * Variation Psv Oc Total Auth Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Dvsa\Olcs\Api\Entity\Licence\Licence;

/**
 * Variation Psv Oc Total Auth Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationPsvOcTotalAuthReviewService extends AbstractVariationOcTotalAuthReviewService
{
    private $licenceTypesWithCommunityLicences = [
        Licence::LICENCE_TYPE_RESTRICTED,
        Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
    ];

    /**
     * Get the keys of the values to compare
     *
     * @param array $data
     * @return string
     */
    protected function getChangedKeys($data)
    {
        $changedKeys['totAuthVehicles'] = 'vehicles';

        if (in_array($data['licenceType']['id'], $this->licenceTypesWithCommunityLicences)) {
            $changedKeys['totCommunityLicences'] = 'community-licences';
        }

        return $changedKeys;
    }
}
