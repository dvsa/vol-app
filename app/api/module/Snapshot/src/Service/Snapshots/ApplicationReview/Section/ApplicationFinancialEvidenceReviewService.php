<?php

/**
 * Application Financial Evidence Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Dvsa\Olcs\Api\Domain\QueryHandlerManager;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Transfer\Query\Application\FinancialEvidence;

/**
 * Application Financial Evidence Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationFinancialEvidenceReviewService extends AbstractReviewService
{
    /**
     * Create service instance
     *
     * @param AbstractReviewServiceServices $abstractReviewServiceServices
     * @param QueryHandlerManager $queryHandlerManager
     *
     * @return ApplicationFinancialEvidenceReviewService
     */
    public function __construct(
        AbstractReviewServiceServices $abstractReviewServiceServices,
        private readonly QueryHandlerManager $queryHandlerManager
    ) {
        parent::__construct($abstractReviewServiceServices);
    }

    /**
     * Format the readonly config from the given data
     *
     * @param array $data Application data
     *
     * @return array
     */
    public function getConfigFromData(array $data = [])
    {
        // @NOTE Tmp solution during migration
        $feData = $this->queryHandlerManager
            ->handleQuery(FinancialEvidence::create(['id' => $data['id']]))
            ->serialize();

        $financialEvidenceData = $feData['financialEvidence'];

        return [
            'multiItems' => [
                [
                    [
                        'label' => 'application-review-financial-evidence-no-of-vehicles',
                        'value' => $this->getTotalVehicles($financialEvidenceData)
                    ],
                    [
                        'label' => 'application-review-financial-evidence-required-finance',
                        'value' => $this->formatAmount($financialEvidenceData['requiredFinance'])
                    ],
                    [
                        'label' => 'application-review-financial-evidence-evidence',
                        'noEscape' => true,
                        'value' => $this->getEvidence($data, $feData)
                    ]
                ]
            ]
        ];
    }

    /**
     * Get total number of vehicles
     *
     * @param array $financialEvidenceData Application financial evidence data
     *
     * @return int
     */
    private function getTotalVehicles($financialEvidenceData)
    {
        return (isset($financialEvidenceData['applicationVehicles']) ?
            (int)$financialEvidenceData['applicationVehicles'] : 0) +
            (isset($financialEvidenceData['otherLicenceVehicles']) ?
            (int)$financialEvidenceData['otherLicenceVehicles'] : 0) +
            (isset($financialEvidenceData['otherApplicationVehicles']) ?
            (int)$financialEvidenceData['otherApplicationVehicles'] : 0);
    }

    /**
     * Get financial evidence document list
     *
     * @param array $data   Application data
     * @param array $feData Financial evidence data
     *
     * @return string
     */
    private function getEvidence($data, $feData)
    {
        if ($data['financialEvidenceUploaded'] === Application::FINANCIAL_EVIDENCE_SEND_IN_POST) {
            return $this->translate('application-review-financial-evidence-evidence-post');
        } elseif ($data['financialEvidenceUploaded'] === Application::FINANCIAL_EVIDENCE_UPLOAD_LATER) {
            return $this->translate('application-review-financial-evidence-evidence-later');
        }

        $documents = is_array($feData['documents']) ? $feData['documents'] : [];

        return $this->formatDocumentList($documents);
    }

    /**
     * Format document list
     *
     * @param array $documents Document data
     *
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
