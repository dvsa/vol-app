<?php

/**
 * Traffic Area Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section;

/**
 * Traffic Area Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TrafficAreaReviewService extends AbstractReviewService
{
    /**
     * Format the readonly config from the given data
     *
     * @param array $data
     * @return array
     */
    public function getConfigFromData(array $data = [])
    {
        return [
            'header' => 'review-operating-centres-traffic-area-title',
            'multiItems' => [
                [
                    [
                        'label' => 'review-operating-centres-traffic-area',
                        'value' => $data['licence']['trafficArea']['name']
                    ]
                ]
            ]
        ];
    }
}
