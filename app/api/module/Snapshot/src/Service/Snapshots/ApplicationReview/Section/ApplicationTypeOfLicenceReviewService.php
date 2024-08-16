<?php

/**
 * Application Type Of Licence Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Dvsa\Olcs\Api\Entity\System\RefData;

class ApplicationTypeOfLicenceReviewService extends AbstractReviewService
{
    public const GOODS_SI_VEHICLE_TYPE_MAPPINGS = [
        RefData::APP_VEHICLE_TYPE_LGV => 'Yes',
        RefData::APP_VEHICLE_TYPE_MIXED => 'No',
    ];

    /**
     * Format the readonly config from the given data
     *
     * @param array $data
     * @return array
     */
    public function getConfigFromData(array $data = [])
    {
        $config = [
            'multiItems' => [
                [
                    [
                        'label' => 'application-review-type-of-licence-operator-location',
                        'value' => $this->getOperatorLocation($data)
                    ]
                ],
                [
                    [
                        'label' => 'application-review-type-of-licence-licence-type',
                        'value' => $this->formatRefdata($data['licenceType'])
                    ]
                ]
            ]
        ];

        if (
            !empty($data['licenceType']['id']) && $data['licenceType']['id'] === 'ltyp_r' &&
            $data['isGoods'] && $data['niFlag'] === 'N'
        ) {
            $config['multiItems'][] = [
                [
                    'label' => 'application_type-of-licence_licence-type.data.restrictedGuidance',
                    'value' => ''
                ]
            ];
        }

        // We only show operator type for GB, as NI is always goods
        if ($data['niFlag'] === 'N') {
            $config['multiItems'][0][] = [
                'label' => 'application-review-type-of-licence-operator-type',
                'value' => $this->getOperatorType($data)
            ];
        }

        $vehicleType = $data['vehicleType']['id'];
        if (isset(self::GOODS_SI_VEHICLE_TYPE_MAPPINGS[$vehicleType])) {
            $config['multiItems'][1][] = [
                'label' => 'application-review-type-of-licence-vehicle-type',
                'value' => self::GOODS_SI_VEHICLE_TYPE_MAPPINGS[$vehicleType],
            ];

            if ($data['lgvDeclarationConfirmation']) {
                $config['multiItems'][] = [
                    [
                        'label' => 'application-review-type-of-licence-lgv-declaration-confirmation',
                        'value' => 'Confirmed'
                    ]
                ];
            }
        }

        return $config;
    }

    private function getOperatorLocation($data)
    {
        return $data['niFlag'] === 'N' ? 'Great Britain' : 'Northern Ireland';
    }

    private function getOperatorType($data)
    {
        return $data['isGoods'] ? 'Goods' : 'PSV';
    }
}
