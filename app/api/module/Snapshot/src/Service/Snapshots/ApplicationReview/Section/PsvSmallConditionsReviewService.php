<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section;

class PsvSmallConditionsReviewService extends AbstractReviewService
{
    public function getConfigFromData(array $data = []): array
    {
        return [
            'mainItems' => [
                [
                    'multiItems' => $this->addSection15cd($data),
                ],
            ],
        ];
    }

    protected function addSection15cd(array $data): array
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
}
