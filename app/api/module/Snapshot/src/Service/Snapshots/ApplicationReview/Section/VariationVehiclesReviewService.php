<?php

/**
 * Variation Vehicles Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section;

/**
 * Variation Vehicles Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationVehiclesReviewService extends AbstractReviewService
{
    /**
     * Format the readonly config from the given data
     *
     * @param array $data
     * @return array
     */
    public function getConfigFromData(array $data = [])
    {
        $multiItems = [];

        foreach ($data['licenceVehicles'] as $vehicle) {
            $multiItems[] = [
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

        return [
            'subSections' => [
                [
                    'mainItems' => [
                        [
                            'multiItems' => $multiItems
                        ]
                    ]
                ]
            ]
        ];
    }
}
