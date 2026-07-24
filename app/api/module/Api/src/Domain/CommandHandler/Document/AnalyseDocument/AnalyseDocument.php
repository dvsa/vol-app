<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Document\AnalyseDocument;

use Dvsa\Olcs\Api\Domain\ConfigAwareInterface;
use Dvsa\Olcs\Api\Domain\ConfigAwareTrait;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Api\Service\EventBridge\EventBridge;
use Dvsa\Olcs\Api\Service\EventBridge\Events\EventInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;

abstract class AnalyseDocument extends AbstractCommandHandler implements TransactionedInterface, ConfigAwareInterface
{
    use ConfigAwareTrait;

    protected readonly EventBridge $eventBridgeService;

    protected $repoServiceName = Repository\Document::class;

    public function __construct(EventBridge $eventBridgeService)
    {
        $this->eventBridgeService = $eventBridgeService;
    }

    #[\Override]
    public function handleCommand(CommandInterface $command)
    {
        /** @var Entity\Application\Application $application */
        $application = $command->getApplication();

        /** @var Entity\Doc\Document|null $providedDocument */
        $providedDocument = $command->getDocument();
        $document = $providedDocument ?? $this->resolveDocumentForApplication($application);

        $this->eventBridgeService->emit($this->createEvent($application, $document));

        return $this->result;
    }

    protected function resolveDocumentForApplication(Entity\Application\Application $application): Entity\Doc\Document
    {
        $document = $this->findDocumentForApplication($application);

        if ($document === null) {
            throw new NotFoundException($this->getMissingDocumentMessage($application));
        }

        return $document;
    }

    protected function getDocumentStoreBucket(): string
    {
        return (string)($this->getConfig()['document_share']['s3']['bucket'] ?? '');
    }

    protected function getDocumentStoreKey(Entity\Doc\Document $document): string
    {
        $key = ltrim((string)$document->getIdentifier(), '/');
        $prefix = trim((string)($this->getConfig()['document_share']['s3']['key_prefix'] ?? ''), '/');

        if ($prefix !== '') {
            return $prefix . '/' . $key;
        }

        return $key;
    }

    protected function buildAnalysisToken(string $prefix, Entity\Application\Application $application, Entity\Doc\Document $document): string
    {
        return sprintf('%s-%d-%d', $prefix, $application->getId(), $document->getId());
    }

    protected function buildBaseApplicantProfile(Entity\Application\Application $application): array
    {
        return [
            'applicationId' => $application->getId(),
        ];
    }

    abstract protected function findDocumentForApplication(Entity\Application\Application $application): ?Entity\Doc\Document;

    abstract protected function buildApplicantProfile(Entity\Application\Application $application): array;

    abstract protected function getMissingDocumentMessage(Entity\Application\Application $application): string;

    abstract protected function createEvent(
        Entity\Application\Application $application,
        Entity\Doc\Document $document
    ): EventInterface;
}
