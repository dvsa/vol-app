<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section;

class PsvMainOccupationUndertakingsReviewService extends AbstractReviewService
{
    public function getConfigFromData(array $data = []): array
    {
        return [
            'multiItems' => [
                [
                    [
                        'label' => 'review-psv-main-occupation-records-label',
                        'value' => $this->formatConfirmed($data['psvOccupationRecordsConfirmation']),
                    ],
                ],
                [
                    [
                        'label' => 'review-psv-main-occupation-income-label',
                        'value' => $this->formatConfirmed($data['psvIncomeRecordsConfirmation']),
                    ],
                ],
            ],
        ];
    }
}
