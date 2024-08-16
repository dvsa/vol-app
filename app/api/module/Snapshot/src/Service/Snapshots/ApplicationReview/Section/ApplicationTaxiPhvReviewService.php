<?php

/**
 * Application Taxi Phv Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section;

/**
 * Application Taxi Phv Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationTaxiPhvReviewService extends AbstractReviewService
{
    /**
     * Format the readonly config from the given data
     *
     * @param array $data
     * @return array
     */
    public function getConfigFromData(array $data = [])
    {
        $mainItems = [];

        foreach ($data['licence']['privateHireLicences'] as $phvLicence) {
            $mainItems[] = [
                'header' => $phvLicence['privateHireLicenceNo'],
                'multiItems' => [
                    [
                        [
                            'label' => 'application-review-taxi-phv-licence-number',
                            'value' => $phvLicence['privateHireLicenceNo']
                        ],
                        [
                            'label' => 'application-review-taxi-phv-council-name',
                            'value' => $phvLicence['contactDetails']['description']
                        ],
                        [
                            'label' => 'application-review-taxi-phv-address',
                            'value' => $this->formatFullAddress($phvLicence['contactDetails']['address'])
                        ]
                    ]
                ]
            ];
        }

        return [
            'subSections' => [
                [
                    'title' => 'application-review-taxi-phv-title',
                    'mainItems' => $mainItems
                ],
                [
                    'title' => 'application-review-taxi-phv-traffic-area-title',
                    'mainItems' => [
                        [
                            'multiItems' => [
                                [
                                    [
                                        'label' => 'application-review-taxi-phv-traffic-area',
                                        'value' => $data['licence']['trafficArea']['name']
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}
