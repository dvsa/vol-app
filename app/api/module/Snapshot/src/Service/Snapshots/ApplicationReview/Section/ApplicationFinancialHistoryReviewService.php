<?php

/**
 * Application Financial History Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;

/**
 * Application Financial History Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationFinancialHistoryReviewService extends AbstractReviewService
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

        $config = [
            'multiItems' => [
                [
                    [
                        'label' => 'application-review-financial-history-bankrupt',
                        'value' => $this->formatYesNo($data['bankrupt'])
                    ]
                ],
                [
                    [
                        'label' => 'application-review-financial-history-liquidation',
                        'value' => $this->formatYesNo($data['liquidation'])
                    ]
                ],
                [
                    [
                        'label' => 'application-review-financial-history-receivership',
                        'value' => $this->formatYesNo($data['receivership'])
                    ]
                ],
                [
                    [
                        'label' => 'application-review-financial-history-administration',
                        'value' => $this->formatYesNo($data['administration'])
                    ]
                ],
                [
                    [
                        'label' => 'application-review-financial-history-disqualified',
                        'value' => $this->formatYesNo($data['disqualified'])
                    ]
                ]
            ]
        ];

        $showAdditionalInfo = false;

        $questions = ['bankrupt', 'liquidation', 'receivership', 'administration', 'disqualified'];

        foreach ($questions as $question) {
            if ($data[$question] == 'Y') {
                $showAdditionalInfo = true;
                break;
            }
        }

        if ($showAdditionalInfo) {
            $config['multiItems'][] = [
                [
                    'label' => 'application-review-financial-history-insolvencyDetails',
                    'value' => $data['insolvencyDetails']
                ]
            ];

            $config['multiItems'][] = [
                [
                    'label' => 'application-review-financial-history-evidence',
                    'noEscape' => true,
                    'value' => $this->formatEvidence($data)
                ]
            ];
        }

        $variationType = $data['variationType']['id'] ?? null;

        if ($variationType !== ApplicationEntity::VARIATION_TYPE_DIRECTOR_CHANGE) {
            $config['multiItems'][] = [
                [
                    'label' => 'application-review-financial-history-insolvencyConfirmation',
                    'value' => $this->formatConfirmed($data['insolvencyConfirmation'])
                ]
            ];
        }

        return $config;
    }

    /**
     * Format evidence details
     *
     * @param array $data data
     *
     * @return string
     */
    private function formatEvidence($data)
    {
        $files = $this->findFiles(
            $data['documents'],
            Category::CATEGORY_LICENSING,
            Category::DOC_SUB_CATEGORY_LICENCE_INSOLVENCY_DOCUMENT_DIGITAL
        );

        if (empty($files)) {
            return $this->translate('application-review-financial-history-evidence-send');
        }

        $fileNames = [];

        foreach ($files as $file) {
            $fileNames[] = $file['description'];
        }

        return implode('<br>', $fileNames);
    }
}
