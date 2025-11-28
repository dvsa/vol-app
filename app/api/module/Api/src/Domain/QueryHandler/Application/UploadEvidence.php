<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Application;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\ApplicationOperatingCentre;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Entity;

/**
 * Upload Evidence
 */
class UploadEvidence extends AbstractQueryHandler
{
    protected $repoServiceName = 'Application';

    protected $extraRepos = ['ApplicationOperatingCentre'];

    /**
     * Handle query
     *
     * @param \Dvsa\Olcs\Transfer\Query\Application\UploadEvidence $query Query DTO
     *
     * @return array
     */
    public function handleQuery(QueryInterface $query)
    {
        /** @var Entity\Application\Application $application */
        $application = $this->getRepo()->fetchById($query->getId());
        /** @var \Dvsa\Olcs\Api\Domain\Repository\Application $applicationRepo */
        $applicationRepo = $this->getRepo();

        $financialEvidenceDocuments = $application->getPostSubmissionApplicationDocuments(
            $applicationRepo->getCategoryReference(Entity\System\Category::CATEGORY_APPLICATION),
            $applicationRepo->getSubCategoryReference(
                Entity\System\SubCategory::DOC_SUB_CATEGORY_FINANCIAL_EVIDENCE_DIGITAL
            )
        );

        $supportingEvidenceDocuments = $application->getPostSubmissionApplicationDocuments(
            $applicationRepo->getCategoryReference(Entity\System\Category::CATEGORY_APPLICATION),
            $applicationRepo->getSubCategoryReference(
                Entity\System\SubCategory::DOC_SUB_CATEGORY_SUPPORTING_EVIDENCE
            )
        );

        /** @var ApplicationOperatingCentre $aopRepo */
        $aopRepo = $this->getRepo('ApplicationOperatingCentre');

        // get list of application operating centres order by addresss
        $aocs = $aopRepo->fetchByApplicationOrderByAddress($query->getId());
        // filter, so only ones requiring ad uploads are returned
        $aocsRequireUpload = $application->getApplicationOperatingCentresEvidenceRequired($aocs);

        return [
            'financialEvidence' => [
                'canAdd' => $application->canAddFinancialEvidence(),
                'documents' => $this->resultList($financialEvidenceDocuments),
            ],
            'operatingCentres' => $this->resultList(
                $aocsRequireUpload,
                ['operatingCentre' => ['address', 'adDocuments' => ['application']]]
            ),
            'supportingEvidence' => $this->resultList($supportingEvidenceDocuments)
        ];
    }
}
