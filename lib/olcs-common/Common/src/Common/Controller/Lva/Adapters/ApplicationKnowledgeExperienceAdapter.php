<?php

declare(strict_types=1);

namespace Common\Controller\Lva\Adapters;

use Common\Service\Cqrs\Query\CachingQueryService;
use Common\Service\Data\CategoryDataService as Category;
use Dvsa\Olcs\Transfer\Query\Application\KnowledgeExperience;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Psr\Container\ContainerInterface;

class ApplicationKnowledgeExperienceAdapter
{
    protected $applicationData;

    public function __construct(
        protected ContainerInterface $container
    ) {
    }

    public function getDocuments(int $applicationId): array
    {
        $documents = $this->getData($applicationId)['documents'] ?? [];

        return is_array($documents) ? $documents : [];
    }

    public function getUploadMetaData(
        array $file,
        int $applicationId
    ): array {
        $licenceId = $this->getData($applicationId)['licence']['id'];

        return [
            'application' => $applicationId,
            'description' => $file['name'],
            'category' => Category::CATEGORY_APPLICATION,
            'subCategory' =>
                Category::DOC_SUB_CATEGORY_KNOWLEDGE_EXPERIENCE_EVIDENCE_DIGITAL,
            'licence' => $licenceId,
        ];
    }

    public function getData(
        int $applicationId,
        bool $noCache = false
    ): array {
        if ($this->applicationData === null || $noCache) {
            $query = $this->container
                ->get(AnnotationBuilder::class)
                ->createQuery(
                    KnowledgeExperience::create([
                        'id' => $applicationId,
                    ])
                );

            $response = $this->container
                ->get(CachingQueryService::class)
                ->send($query);

            $this->applicationData = $response->getResult();
        }

        return $this->applicationData;
    }
}
