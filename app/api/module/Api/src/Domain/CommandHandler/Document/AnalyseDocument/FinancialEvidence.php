<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Document\AnalyseDocument;

use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Api\Service\EventBridge\EventBridge;
use Dvsa\Olcs\Api\Service\EventBridge\Events\AnalyseFinancialEvidenceDocument;

class FinancialEvidence extends AnalyseDocument
{
    public function __construct(EventBridge $eventBridgeService)
    {
        parent::__construct($eventBridgeService);
    }

    #[\Override]
    protected function findDocumentForApplication(Entity\Application\Application $application): ?Entity\Doc\Document
    {
        /** @var Repository\Document $documentRepo */
        $documentRepo = $this->getRepo();

        return $documentRepo->fetchLatestFinancialEvidenceForApplication($application);
    }

    #[\Override]
    protected function getMissingDocumentMessage(Entity\Application\Application $application): string
    {
        return sprintf('No financial evidence document found for application %d', $application->getId());
    }

    #[\Override]
    protected function createEvent(
        Entity\Application\Application $application,
        Entity\Doc\Document $document
    ): \Dvsa\Olcs\Api\Service\EventBridge\Events\EventInterface {
        return new AnalyseFinancialEvidenceDocument(
            $this->buildAnalysisToken('financial-evidence', $application, $document),
            $this->getDocumentStoreBucket(),
            $this->getDocumentStoreKey($document),
            $this->buildApplicantProfile($application)
        );
    }

    protected function buildApplicantProfile(Entity\Application\Application $application): array
    {
        return $this->buildBaseApplicantProfile($application);
    }
}
