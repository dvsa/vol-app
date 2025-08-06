<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section;

class PsvSmallPartWrittenReviewService extends AbstractReviewService
{
    public function getConfigFromData(array $data = []): array
    {
        return [
            'multiItems' => [
                [
                    [
                        'label' => 'application_psv_written_evidence.title',
                        'value' => $this->formatText($data['psvSmallVhlNotes']),
                    ],
                ],
                [
                    [
                        'label' => 'application_written-evidence.eightSeats.label',
                        'value' => $data['psvTotalVehicleSmall'],
                    ],
                ],
                [
                    [
                        'label' => 'application_written-evidence.nineSeats.label',
                        'value' => $data['psvTotalVehicleLarge'],
                    ],
                ],
            ],
        ];
    }
}
