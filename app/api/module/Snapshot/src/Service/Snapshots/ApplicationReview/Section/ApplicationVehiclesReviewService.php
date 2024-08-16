<?php

/**
 * Application Vehicles Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section;

/**
 * Application Vehicles Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationVehiclesReviewService extends AbstractReviewService
{
    /**
     * Format the readonly config from the given data
     *
     * @param array $data
     * @return array
     */
    public function getConfigFromData(array $data = [])
    {
        $mainItems = [
            [
                'multiItems' => [
                    [
                        [
                            'label' => 'application-review-vehicles-hasEnteredReg',
                            'value' => $this->formatYesNo($data['hasEnteredReg'])
                        ]
                    ]
                ]
            ]
        ];

        if ($data['hasEnteredReg'] == 'Y') {
            foreach ($data['licenceVehicles'] as $vehicle) {
                $mainItems[0]['multiItems'][] = [
                    [
                        'label' => 'application-review-vehicles-vrm',
                        'value' => $vehicle['vehicle']['vrm']
                    ],
                    [
                        'label' => 'application-review-vehicles-weight',
                        'value' => $this->formatNumber($vehicle['vehicle']['platedWeight']) . ' kg'
                    ]
                ];
            }
        }

        return [
            'subSections' => [
                [
                    'mainItems' => $mainItems
                ]
            ]
        ];
    }
}
