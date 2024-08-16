<?php

/**
 * Application Convictions Penalties Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;

/**
 * Application Convictions Penalties Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationConvictionsPenaltiesReviewService extends AbstractReviewService
{
    /**
     * Format the readonly config from the given data
     *
     * @param array $data data
     *
     * @return array
     */
    public function getConfigFromData(array $data = [])
    {
        if ($data['prevConviction'] == 'N') {
            $multiItemsArray['multiItems'][] = [
                [
                    'label' => 'application-review-convictions-penalties-question',
                    'value' => $this->formatYesNo($data['prevConviction'])
                ]
            ];

            $variationType = $data['variationType']['id'] ?? null;

            if ($variationType != ApplicationEntity::VARIATION_TYPE_DIRECTOR_CHANGE) {
                $multiItemsArray['multiItems'][] = [
                    [
                        'label' => 'application-review-convictions-penalties-confirmation',
                        'value' => $this->formatConfirmed($data['convictionsConfirmation'])
                    ]
                ];
            }
            return $multiItemsArray;
        }

        $mainItems = [];

        foreach ($data['previousConvictions'] as $conviction) {
            $mainItems[] = [
                'header' => $conviction['forename'] . ' ' . $conviction['familyName'],
                'multiItems' => [
                    [
                        [
                            'label' => 'application-review-convictions-penalties-conviction-title',
                            'value' => $this->formatRefData($conviction['title'])
                        ],
                        [
                            'label' => 'application-review-convictions-penalties-conviction-forename',
                            'value' => $conviction['forename']
                        ],
                        [
                            'label' => 'application-review-convictions-penalties-conviction-familyName',
                            'value' => $conviction['familyName']
                        ],
                        [
                            'label' => 'application-review-convictions-penalties-conviction-convictionDate',
                            'value' => $this->formatDate($conviction['convictionDate'])
                        ],
                        [
                            'label' => 'application-review-convictions-penalties-conviction-offence',
                            'value' => $conviction['categoryText']
                        ],
                        [
                            'label' => 'application-review-convictions-penalties-conviction-offence-details',
                            'value' => $conviction['notes']
                        ],
                        [
                            'label' => 'application-review-convictions-penalties-conviction-offence-court',
                            'value' => $conviction['courtFpn']
                        ],
                        [
                            'label' => 'application-review-convictions-penalties-conviction-offence-penalty',
                            'value' => $conviction['penalty']
                        ]
                    ]
                ]
            ];
        }

        if ($data['variationType']['id'] !== ApplicationEntity::VARIATION_TYPE_DIRECTOR_CHANGE) {
            $mainItems[] = [
                'multiItems' => [
                    [], // Adds a separator
                    [
                        [
                            'label' => 'application-review-convictions-penalties-confirmation',
                            'value' => $this->formatConfirmed($data['convictionsConfirmation'])
                        ]
                    ]
                ]
            ];
        }

        return [
            'subSections' => [
                [
                    'mainItems' => $mainItems
                ]
            ]
        ];
    }
}
