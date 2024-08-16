<?php

/**
 * People Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Dvsa\Olcs\Api\Entity\Organisation\Organisation;

/**
 * People Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class PeopleReviewService extends AbstractReviewService
{
    /**
     * Format the readonly config from the given data,
     *  Overloaded with showPosition bool
     *
     * @param array $data
     * @param bool $showPosition
     * @return array
     */
    public function getConfigFromData(array $data = [], $showPosition = false)
    {
        $item = [
            'header' => $data['person']['forename'] . ' ' . $data['person']['familyName'],
            'multiItems' => [
                [
                    [
                        'label' => 'application-review-people-person-title',
                        'value' => $this->formatRefData($data['person']['title'])
                    ],
                    [
                        'label' => 'application-review-people-person-firstname',
                        'value' => $data['person']['forename']
                    ],
                    [
                        'label' => 'application-review-people-person-lastname',
                        'value' => $data['person']['familyName']
                    ],
                    [
                        'label' => 'application-review-people-person-othername',
                        'value' => $data['person']['otherName']
                    ],
                    [
                        'label' => 'application-review-people-person-dob',
                        'value' => $this->formatDate($data['person']['birthDate'])
                    ]
                ]
            ]
        ];

        if ($showPosition) {
            $item['multiItems'][0][] = [
                'label' => 'application-review-people-person-position',
                'value' => $data['position']
            ];
        }

        return $item;
    }

    public function shouldShowPosition($data)
    {
        return $data['licence']['organisation']['type']['id'] === Organisation::ORG_TYPE_OTHER;
    }
}
