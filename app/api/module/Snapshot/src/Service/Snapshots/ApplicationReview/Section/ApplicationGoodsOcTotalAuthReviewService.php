<?php

/**
 * Application Goods Oc Total Auth Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Dvsa\Olcs\Api\Entity\System\RefData;

/**
 * Application Goods Oc Total Auth Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationGoodsOcTotalAuthReviewService extends AbstractReviewService
{
    public const OTHER_ROW_DEFINITIONS = [
        [
            'label' => 'review-operating-centres-authorisation-trailers',
            'sourceKey' => 'totAuthTrailers',
            'vehicleTypeIds' => [
                RefData::APP_VEHICLE_TYPE_HGV,
                RefData::APP_VEHICLE_TYPE_MIXED,
            ]
        ],
        [
            'label' => 'review-operating-centres-authorisation-community-licences',
            'sourceKey' => 'totCommunityLicences',
            'vehicleTypeIds' => [
                RefData::APP_VEHICLE_TYPE_MIXED,
                RefData::APP_VEHICLE_TYPE_LGV,
            ]
        ]
    ];

    /**
     * Get total auth config
     *
     * @param array $data
     * @return array
     */
    public function getConfigFromData(array $data = [])
    {
        $multiItems = [];
        $vehicleTypeId = $data['vehicleType']['id'];

        if (
            ($vehicleTypeId == RefData::APP_VEHICLE_TYPE_HGV) ||
            ($vehicleTypeId == RefData::APP_VEHICLE_TYPE_MIXED && is_null($data['totAuthLgvVehicles']))
        ) {
            $multiItems[] = [
                'label' => 'review-operating-centres-authorisation-vehicles',
                'value' => $data['totAuthVehicles']
            ];
        }

        if ($vehicleTypeId == RefData::APP_VEHICLE_TYPE_MIXED && !is_null($data['totAuthLgvVehicles'])) {
            $multiItems[] = [
                'label' => 'review-operating-centres-authorisation-heavy-goods-vehicles',
                'value' => $data['totAuthHgvVehicles']
            ];
        }

        if (
            $vehicleTypeId == (RefData::APP_VEHICLE_TYPE_LGV) ||
            ($vehicleTypeId == RefData::APP_VEHICLE_TYPE_MIXED && !is_null($data['totAuthLgvVehicles']))
        ) {
            $multiItems[] = [
                'label' => 'review-operating-centres-authorisation-light-goods-vehicles',
                'value' => $data['totAuthLgvVehicles']
            ];
        }

        foreach (self::OTHER_ROW_DEFINITIONS as $definition) {
            if (in_array($vehicleTypeId, $definition['vehicleTypeIds'])) {
                $multiItems[] = [
                    'label' => $definition['label'],
                    'value' => $data[$definition['sourceKey']]
                ];
            }
        }

        return [
            'header' => 'review-operating-centres-authorisation-title',
            'multiItems' => [$multiItems]
        ];
    }
}
