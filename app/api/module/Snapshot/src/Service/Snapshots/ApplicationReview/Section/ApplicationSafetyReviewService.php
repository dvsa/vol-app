<?php

/**
 * Application Safety Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SubCategory;

/**
 * Application Safety Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationSafetyReviewService extends AbstractReviewService
{
    /**
     * Format the readonly config from the given data
     *
     * @param array $data
     * @return array
     */
    public function getConfigFromData(array $data = [])
    {
        $multiItems = [
            'safetyIns' => [
                [
                    'label' => 'application-review-safety-safetyInsVehicles',
                    'value' => $this->formatDuration($data['licence']['safetyInsVehicles'])
                ]
            ],
            'safetyInsVaries' => [
                [
                    'label' => 'application-review-safety-safetyInsVaries',
                    'value' => $this->formatYesNo($data['licence']['safetyInsVaries'])
                ]
            ],
            [
                [
                    'label' => 'application-review-safety-tachographIns',
                    'value' => $this->translate('tachograph_analyser.' . $data['licence']['tachographIns']['id'])
                ],
                [
                    'label' => 'application-review-safety-tachographInsName-snapshot',
                    'value' => $data['licence']['tachographInsName']
                ]
            ],
            [
                [
                    'label' => 'application-review-safety-additional-information',
                    'noEscape' => true,
                    'value' => $this->getSafetyDocuments($data['documents'])
                ]
            ],
            [
                [
                    'label' => 'application-review-safety-safetyConfirmation',
                    'value' => $this->formatConfirmed($data['safetyConfirmation'])
                ]
            ]
        ];

        if (!$this->isPsv($data)) {
            if (!empty($data['totAuthTrailers'])) {
                $multiItems['safetyIns'][] = [
                    'label' => 'application-review-safety-safetyInsTrailers',
                    'value' => $this->formatDuration($data['licence']['safetyInsTrailers'])
                ];
            }
        } else {
            $multiItems['safetyInsVaries'][0]['label'] .= '-psv';
        }

        $config = [
            'subSections' => [
                [
                    'mainItems' => [
                        [
                            'multiItems' => $multiItems
                        ]
                    ]
                ],
                [
                    'title' => 'application-review-safety-workshop-title',
                    'mainItems' => $this->getSafetyProviders($data)
                ]
            ]
        ];

        return $config;
    }

    private function getSafetyProviders($data)
    {
        $list = [];

        foreach ($data['licence']['workshops'] as $workshop) {
            $list[] = [
                'header' => $this->formatShortAddress($workshop['contactDetails']['address']),
                'multiItems' => [
                    [
                        [
                            'label' => 'application-review-safety-workshop-isExternal',
                            'value' => $this->formatIsExternal($workshop)
                        ],
                        [
                            'label' => 'application-review-safety-workshop-name',
                            'value' => $workshop['contactDetails']['fao']
                        ],
                        [
                            'label' => 'application-review-safety-workshop-address',
                            'value' => $this->formatFullAddress($workshop['contactDetails']['address'])
                        ]
                    ]
                ]
            ];
        }

        return $list;
    }

    private function formatIsExternal($workshop)
    {
        return $this->translate('application-review-safety-workshop-isExternal-' . $workshop['isExternal']);
    }

    private function formatDuration($value)
    {
        if ($value == 0) {
            return $this->translate('N/A');
        }

        if ($value == 1) {
            return $this->translate('1 Week');
        }

        return $this->translateReplace('no.of.weeks', [$value]);
    }

    /**
     * Get safety documents
     *
     * @param array $docs
     * @return string
     */
    private function getSafetyDocuments($docs)
    {
        $files = $this->findFiles(
            $docs,
            Category::CATEGORY_APPLICATION,
            SubCategory::DOC_SUB_CATEGORY_MAINT_OTHER_DIGITAL
        );
        $documents = is_array($files) ? $files : [];
        return $this->formatDocumentList($documents);
    }

    /**
     * Format document list
     *
     * @param array $documents
     * @return string
     */
    private function formatDocumentList($documents)
    {
        $files = [];

        foreach ($documents as $document) {
            $files[] = $document['description'];
        }

        return implode('<br>', $files);
    }
}
