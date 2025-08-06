<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section;

class VehiclesSizeReviewService extends AbstractReviewService
{
    public function getConfigFromData(array $data = []): array
    {
        return [
            'multiItems' => [
                [
                    [
                        'label' => 'application-review-vehicles-declarations-vs',
                        'value' => $data['psvWhichVehicleSizes']['description'],
                    ],
                ],
            ],
        ];
    }
}
