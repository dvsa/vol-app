<?php

/**
 * Application Vehicles Psv Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section;

/**
 * Application Vehicles Psv Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationVehiclesPsvReviewService extends AbstractReviewService
{
    /**
     * Create service instance
     *
     * @param AbstractReviewServiceServices $abstractReviewServiceServices
     * @param VehiclesPsvReviewService $vehiclesPsvReviewService
     *
     * @return ApplicationVehiclesPsvReviewService
     */
    public function __construct(
        AbstractReviewServiceServices $abstractReviewServiceServices,
        private readonly VehiclesPsvReviewService $vehiclesPsvReviewService
    ) {
        parent::__construct($abstractReviewServiceServices);
    }

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

        return [
            'subSections' => [
                [
                    'mainItems' => $this->vehiclesPsvReviewService->getConfigFromData($data, $mainItems)
                ]
            ]
        ];
    }
}
