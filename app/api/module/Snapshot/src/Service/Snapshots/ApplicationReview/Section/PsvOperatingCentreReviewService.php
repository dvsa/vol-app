<?php

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section;

/**
 * Psv Operating Centre Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class PsvOperatingCentreReviewService extends AbstractReviewService
{
    /**
     * Format the OC config
     *
     * @param array $data Data from API
     *
     * @return array
     */
    public function getConfigFromData(array $data = [])
    {
        return [
            'header' => $this->formatShortAddress($data['operatingCentre']['address']),
            'multiItems' => [
                [
                    [
                        'label' => 'review-operating-centre-address',
                        'value' => $this->formatFullAddress($data['operatingCentre']['address'])
                    ]
                ],
                'vehicles+trailers' => [
                    [
                        'label' => 'review-operating-centre-total-vehicles',
                        'value' => $data['noOfVehiclesRequired']
                    ]
                ],
                [
                    [
                        'label' => 'review-operating-centre-permission',
                        'value' => $this->formatConfirmed($data['permission'])
                    ]
                ]
            ]
        ];
    }
}
