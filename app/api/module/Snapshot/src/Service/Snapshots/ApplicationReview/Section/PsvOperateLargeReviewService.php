<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section;

class PsvOperateLargeReviewService extends AbstractReviewService
{
    public function getConfigFromData(array $data = []): array
    {
        return [
            'mainItems' => [
                [
                    'multiItems' => [
                        [
                            [
                                'label' => 'application-review-vehicles-declarations-15e',
                                'value' => $this->formatConfirmed($data['psvNoSmallVhlConfirmation']),
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
