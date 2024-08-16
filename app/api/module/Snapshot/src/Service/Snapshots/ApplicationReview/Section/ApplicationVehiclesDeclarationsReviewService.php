<?php

/**
 * Application Vehicles Declarations Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Application\Application;

/**
 * Application Vehicles Declarations Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationVehiclesDeclarationsReviewService extends AbstractReviewService
{
    /**
     * Format the readonly config from the given data
     *
     * @param array $data
     * @return array
     */
    public function getConfigFromData(array $data = [])
    {
        $subSections = [];

        $psvWhichVehicleSize = $data['psvWhichVehicleSizes']['id'] ?? null;

        if ($psvWhichVehicleSize) {
            $subSections[] = [
                'mainItems' => [
                    [
                        'multiItems' => [
                            [
                                [
                                    'label' => 'application-review-vehicles-declarations-vs',
                                    'value' => $data['psvWhichVehicleSizes']['description']
                                ]
                            ]
                        ]
                    ]
                ]
            ];
        }

        // All options relating to having small vehicles
        if (
            $psvWhichVehicleSize === Application::PSV_VEHICLE_SIZE_SMALL ||
            $psvWhichVehicleSize === Application::PSV_VEHICLE_SIZE_BOTH
        ) {
            $multiItems = [];

            // 15b[i]
            if ($data['licence']['trafficArea']['isScotland'] == false) {
                $multiItems['15b'][] = $this->addSection15b1($data);

                // 15b[ii]
                if ($data['psvOperateSmallVhl'] === 'Y') {
                    $multiItems['15b'][] = $this->addSection15b2($data);
                } else {
                    // 15c/d
                    $multiItems = array_merge($multiItems, $this->addSection15cd($data));
                }
            } else {
                // 15c/d
                $multiItems = array_merge($multiItems, $this->addSection15cd($data));
            }

            $subSections[] = [
                'title' => 'application-review-vehicles-declarations-small-title',
                'mainItems' => [
                    [
                        'multiItems' => $multiItems
                    ]
                ]
            ];
        }

        if ($psvWhichVehicleSize === Application::PSV_VEHICLE_SIZE_MEDIUM_LARGE) {
            $subSections[] = [
                'title' => 'application-review-vehicles-declarations-medium-title',
                'mainItems' => [
                    [
                        'multiItems' => [
                            [
                                $this->addSection15e($data)
                            ]
                        ]
                    ]
                ]
            ];
        }

        if (
            $data['licenceType']['id'] === Licence::LICENCE_TYPE_RESTRICTED &&
            $psvWhichVehicleSize !== Application::PSV_VEHICLE_SIZE_SMALL
        ) {
            $subSections[] = [
                'title' => 'application-review-vehicles-declarations-business-title',
                'mainItems' => [
                    [
                        'multiItems' => [
                            [
                                $this->addSection8b1($data)
                            ],
                            [
                                $this->addSection8b2($data)
                            ]
                        ]
                    ]
                ]
            ];
        }

        $multiItems = [];

        $multiItems['15f'][] = $this->addSection15f1($data);

        if ($data['psvLimousines'] === 'Y') {
            if ($psvWhichVehicleSize !== Application::PSV_VEHICLE_SIZE_SMALL) {
                $multiItems['15g'][] = $this->addSection15g();
            }
        } else {
            $multiItems['15f'][] = $this->addSection15f2();
        }

        $subSections[] = [
            'title' => 'application-review-vehicles-declarations-novelty-title',
            'mainItems' => [
                [
                    'multiItems' => $multiItems
                ]
            ]
        ];

        return [
            'subSections' => $subSections
        ];
    }

    protected function addSection15b1($data)
    {
        return [
            'label' => 'application-review-vehicles-declarations-15b1',
            'value' => $this->formatYesNo($data['psvOperateSmallVhl'])
        ];
    }

    protected function addSection15b2($data)
    {
        return [
            'label' => 'application-review-vehicles-declarations-15b2',
            'noEscape' => true,
            'value' => $this->formatText($data['psvSmallVhlNotes'])
        ];
    }

    protected function addSection15cd($data)
    {
        return [
            [
                [
                    'label' => 'application-review-vehicles-declarations-15cd',
                    'value' => $this->formatConfirmed($data['psvSmallVhlConfirmation'])
                ]
            ],
            [
                [
                    'full-content' => $this->translate(
                        'markup-application_vehicle-safety_undertakings-smallVehiclesUndertakingsScotland'
                    )
                ],
            ],
            [
                [
                    'full-content' => '<h4>Undertakings</h4>' . $this->translate(
                        'markup-application_vehicle-safety_undertakings-smallVehiclesUndertakings'
                    )
                ]
            ]
        ];
    }

    protected function addSection15e($data)
    {
        return [
            'label' => 'application-review-vehicles-declarations-15e',
            'value' => $this->formatConfirmed($data['psvNoSmallVhlConfirmation'])
        ];
    }

    protected function addSection8b1($data)
    {
        return [
            'label' => 'application-review-vehicles-declarations-8b1',
            'value' => $this->formatConfirmed($data['psvMediumVhlConfirmation'])
        ];
    }

    protected function addSection8b2($data)
    {
        return [
            'label' => 'application-review-vehicles-declarations-8b2',
            'noEscape' => true,
            'value' => $this->formatText($data['psvMediumVhlNotes'])
        ];
    }

    protected function addSection15f1($data)
    {
        return [
            'label' => 'application-review-vehicles-declarations-15f1',
            'value' => $this->formatYesNo($data['psvLimousines'])
        ];
    }

    protected function addSection15f2()
    {
        return [
            'label' => 'application-review-vehicles-declarations-15f2',
            'value' => $this->formatConfirmed('Y')
        ];
    }

    protected function addSection15g()
    {
        return [
            'label' => 'application-review-vehicles-declarations-15g',
            'value' => $this->formatConfirmed('Y')
        ];
    }
}
