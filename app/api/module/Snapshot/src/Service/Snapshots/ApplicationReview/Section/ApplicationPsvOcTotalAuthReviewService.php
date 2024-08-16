<?php

/**
 * Application Psv Oc Total Auth Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Dvsa\Olcs\Api\Entity\Licence\Licence;

/**
 * Application Psv Oc Total Auth Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationPsvOcTotalAuthReviewService extends AbstractReviewService
{
    private $licenceTypesWithLargeVehicles = [
        Licence::LICENCE_TYPE_STANDARD_NATIONAL,
        Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
    ];

    private $licenceTypesWithCommunityLicences = [
        Licence::LICENCE_TYPE_RESTRICTED,
        Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
    ];

    /**
     * Get total auth config
     *
     * @param array $data
     * @return array
     */
    public function getConfigFromData(array $data = [])
    {
        $config = [
            'header' => 'review-operating-centres-authorisation-title',
            'multiItems' => [
                [
                    [
                        'label' => 'review-operating-centres-authorisation-vehicles',
                        'value' => $data['totAuthVehicles']
                    ]
                ]
            ]
        ];

        // Conditionally add the community licences, if the licence type is restricted or standard international
        if (in_array($data['licenceType']['id'], $this->licenceTypesWithCommunityLicences)) {
            $config['multiItems'][0][] = [
                'label' => 'review-operating-centres-authorisation-community-licences.psv',
                'value' => $data['totCommunityLicences']
            ];
        }

        return $config;
    }
}
