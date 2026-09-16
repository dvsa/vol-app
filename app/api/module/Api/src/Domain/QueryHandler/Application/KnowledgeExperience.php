<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Application;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

final class KnowledgeExperience extends AbstractQueryHandler
{
    protected $repoServiceName = 'Application';

    #[\Override]
    public function handleQuery(QueryInterface $query)
    {
        $applicationRepo = $this->getRepo();

        $application = $applicationRepo->fetchUsingId(
            $query,
            Query::HYDRATE_OBJECT
        );

        $documents = null;

        if (!$this->isReadOnlyInternalUser()) {
            $documents = $application->getApplicationDocuments(
                $applicationRepo->getCategoryReference(
                    Category::CATEGORY_APPLICATION
                ),
                $applicationRepo->getSubCategoryReference(
                    SubCategory::DOC_SUB_CATEGORY_KNOWLEDGE_EXPERIENCE_EVIDENCE_DIGITAL
                )
            );

            $documents = $this->resultList($documents);
        }

        return $this->result(
            $application,
            [
                'licence',
            ],
            [
                'documents' => $documents,
            ]
        );
    }
}
