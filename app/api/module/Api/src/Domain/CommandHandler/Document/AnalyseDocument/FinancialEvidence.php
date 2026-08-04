<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Document\AnalyseDocument;

use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Dvsa\Olcs\Api\Service\EventBridge\Events\EventInterface;
use Dvsa\Olcs\Api\Service\EventBridge\Events\FinancialEvidenceDocumentSubmitted;
use Dvsa\Olcs\Api\Service\Idp\ApplicantProfileBuilder;
use Dvsa\Olcs\Api\Service\EventBridge\EventBridge;
use Dvsa\Olcs\Api\Service\Idp\AnalysisTokenGenerator;

/**
 * @see \Dvsa\OlcsTest\Api\Domain\CommandHandler\Document\AnalyseDocument\FinancialEvidenceTest
 */
class FinancialEvidence extends AnalyseDocument
{
    public function __construct(
        EventBridge $eventBridgeService,
        AnalysisTokenGenerator $tokenGenerator,
        private readonly ApplicantProfileBuilder $applicantProfileBuilder
    ) {
        parent::__construct($eventBridgeService, $tokenGenerator);
    }

    /**
     * Uses the existing query layer rather than a bespoke query, so the document set matches
     * what the financial-evidence screen shows the caseworker.
     *
     * @return Entity\Doc\Document[]
     */
    #[\Override]
    protected function findDocumentsForApplication(Entity\Application\Application $application): array
    {
        $applicationRepo = $this->getRepo('Application');

        $documents = $application->getApplicationDocuments(
            $applicationRepo->getCategoryReference(Category::CATEGORY_APPLICATION),
            $applicationRepo->getSubCategoryReference(SubCategory::DOC_SUB_CATEGORY_FINANCIAL_EVIDENCE_DIGITAL)
        );

        return array_values($documents->toArray());
    }

    #[\Override]
    protected function getNoDocumentsMessage(Entity\Application\Application $application): string
    {
        return sprintf('No financial evidence documents found for application %d', $application->getId());
    }

    #[\Override]
    protected function createEvent(
        Entity\Application\Application $application,
        Entity\Doc\Document $document,
        string $analysisToken
    ): EventInterface {
        return new FinancialEvidenceDocumentSubmitted(
            $analysisToken,
            (int)$application->getId(),
            (int)$document->getId(),
            $this->getDocumentStoreBucket(),
            $this->getDocumentStoreKey($document),
            $this->applicantProfileBuilder->build($application)
        );
    }
}
