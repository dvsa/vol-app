<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section;

class PsvOperateSmallReviewService extends AbstractReviewService
{
    public function getConfigFromData(array $data = []): array
    {
        return [
            'mainItems' => [
                [
                    'multiItems' => [
                        [
                            [
                                'label' => 'application-review-vehicles-declarations-15b1',
                                'value' => $this->formatYesNo($data['psvOperateSmallVhl'])
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
