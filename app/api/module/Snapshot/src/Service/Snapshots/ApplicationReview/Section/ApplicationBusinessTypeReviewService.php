<?php

/**
 * Application Business Type Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section;

/**
 * Application Business Type Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationBusinessTypeReviewService extends AbstractReviewService
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
            'multiItems' => [
                [
                    [
                        'label' => 'application-review-business-type',
                        'value' => $this->formatRefdata($data['licence']['organisation']['type'])
                    ]
                ]
            ]
        ];
    }
}
