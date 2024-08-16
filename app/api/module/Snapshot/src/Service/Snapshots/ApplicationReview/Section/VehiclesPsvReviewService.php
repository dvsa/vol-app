<?php

/**
 * Vehicles Psv Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Dvsa\Olcs\Api\Entity\Vehicle\Vehicle;

/**
 * Vehicles Psv Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VehiclesPsvReviewService extends AbstractReviewService
{
    /**
     * Format the readonly config from the given data
     *
     * @param array $data
     * @return array
     */
    public function getConfigFromData(array $data = [], $mainItems = [])
    {
        if ($data['isVariation'] || $data['hasEnteredReg'] == 'Y') {
            $mainItems[] = $this->formatVehicles($data['licenceVehicles']);
        }

        return $mainItems;
    }

    private function formatVehicles($licenceVehicles)
    {
        $items = [];

        foreach ($licenceVehicles as $licenceVehicle) {
            $items[] = [
                [
                    'label' => 'application-review-vehicles-vrm',
                    'value' => $licenceVehicle['vehicle']['vrm']
                ],
                [
                    'label' => 'application-review-vehicles-make',
                    'value' => $licenceVehicle['vehicle']['makeModel']
                ]
            ];
        }

        return [
            'header' => 'application-review-vehicles-psv-title',
            'multiItems' => $items
        ];
    }
}
