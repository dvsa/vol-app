<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Dvsa\Olcs\Api\Domain\QueryHandlerManager;
use Dvsa\Olcs\Transfer\Query\Application\KnowledgeExperience;

final class ApplicationKnowledgeExperienceReviewService extends AbstractReviewService
{
    public function __construct(
        AbstractReviewServiceServices $abstractReviewServiceServices,
        private readonly QueryHandlerManager $queryHandlerManager
    ) {
        parent::__construct($abstractReviewServiceServices);
    }

    #[\Override]
    public function getConfigFromData(array $data = [])
    {
        $knowledgeExperienceData = $this->queryHandlerManager
            ->handleQuery(KnowledgeExperience::create(['id' => $data['id']]))
            ->serialize();

        if ($knowledgeExperienceData['knowledgeExperienceOlat'] === 'Y') {
            return [
                'multiItems' => [
                    [
                        [
                            'label' => 'application-review-knowledge-experience-olat',
                            'value' => $this->translate('Yes'),
                        ],
                    ],
                ],
            ];
        }

        $documents = is_array($knowledgeExperienceData['documents'])
            ? $knowledgeExperienceData['documents']
            : [];

        return [
            'multiItems' => [
                [
                    [
                        'label' => 'application-review-knowledge-experience-evidence',
                        'noEscape' => true,
                        'value' => $this->formatDocumentList($documents),
                    ],
                ],
            ],
        ];
    }

    private function formatDocumentList(array $documents): string
    {
        $files = [];

        foreach ($documents as $document) {
            $files[] = $document['description'];
        }

        return implode('<br>', $files);
    }
}
