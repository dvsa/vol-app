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
        $returnData = [
            [
                [
                    'label' => 'application-review-vehicles-declarations-15cd',
                    'value' => $this->formatConfirmed($data['psvSmallVhlConfirmation'])
                ],
            ],
            [
                [
                    'full-content' => $this->translate(
                        'application_vehicle-safety_undertakings.smallVehiclesUndertakingsScotland.title'
                    )
                ],
            ],
        ];

        if ($data['isOperatingSmallPsvAsPartOfLarge'] === false) {
            $returnData[] = [
                [
                    'full-content' => $this->translate(
                        'markup-application_vehicle-safety_undertakings-smallVehiclesUndertakingsScotland'
                    )
                ],
            ];
        }

        $undertakingsHeading = $this->translate('application_vehicle-safety_undertakings.smallVehiclesUndertakings.title');

        $returnData[] = [
            [
                'full-content' => '<h4>' . $undertakingsHeading . '</h4>' . $this->translate(
                    'markup-application_vehicle-safety_undertakings-smallVehiclesUndertakings'
                )
            ],
        ];

        return $returnData;
    }
}
