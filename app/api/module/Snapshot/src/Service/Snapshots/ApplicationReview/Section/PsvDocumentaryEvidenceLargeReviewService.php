<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Dvsa\Olcs\Api\Domain\QueryHandlerManager;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Dvsa\Olcs\Transfer\Query\Application\Documents;

class PsvDocumentaryEvidenceLargeReviewService extends AbstractReviewService
{
    public function __construct(
        AbstractReviewServiceServices $abstractReviewServiceServices,
        private readonly QueryHandlerManager $queryHandlerManager
    ) {
        parent::__construct($abstractReviewServiceServices);
    }

    public function getConfigFromData(array $data = []): array
    {
        $queryData = [
            'id' => $data['id'],
            'category' => Category::CATEGORY_APPLICATION,
            'subCategory' => SubCategory::DOC_SUB_CATEGORY_LARGE_PSV_EVIDENCE_DIGITAL,
        ];

        $evidenceData = $this->queryHandlerManager->handleQuery(Documents::create($queryData));

        return [
            'multiItems' => [
                [
                    [
                        'label' => 'application-review-financial-evidence-evidence',
                        'noEscape' => true,
                        'value' => $this->getEvidence($data, $evidenceData)
                    ]
                ]
            ]
        ];
    }

    private function getEvidence(array $data, array $evidenceData): string
    {
        if ($data['occupationEvidenceUploaded'] === Application::FINANCIAL_EVIDENCE_UPLOAD_LATER) {
            return $this->translate('application-review-evidence-later');
        }

        $documents = is_array($evidenceData) ? $evidenceData : [];

        return $this->formatDocumentList($documents);
    }

    private function formatDocumentList(array $documents): string
    {
        $files = [];

        foreach ($documents as $document) {
            $files[] = $document['description'];
        }

        return implode('<br />', $files);
    }
}
