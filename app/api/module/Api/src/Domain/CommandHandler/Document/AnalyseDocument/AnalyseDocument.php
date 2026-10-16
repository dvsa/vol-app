<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Document\AnalyseDocument;

use Dvsa\Olcs\Api\Domain\Command\Document\AnalyseDocumentCommandInterface;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\ConfigAwareInterface;
use Dvsa\Olcs\Api\Domain\ConfigAwareTrait;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Api\Service\EventBridge\EventBridge;
use Dvsa\Olcs\Api\Service\EventBridge\Events\EventInterface;
use Dvsa\Olcs\Api\Service\Idp\AnalysisTokenGenerator;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Olcs\Logging\Log\Logger;

/**
 * Given an application id, resolves its evidence documents and requests analysis of each.
 *
 * Two things here are load-bearing:
 *
 * - The row is persisted before the event is emitted, so anything that loses the work - a
 *   failed emit, lost delivery, a dead consumer - leaves a stale PENDING row for the sweeper.
 * - This must not implement TransactionedInterface. Doctrine nests rather than isolates
 *   transactions, so an inner rollback marks the connection rollback-only and fails the
 *   caller's commit; a failure here has to stay containable by the caller.
 */
abstract class AnalyseDocument extends AbstractCommandHandler implements ConfigAwareInterface
{
    use ConfigAwareTrait;

    protected $repoServiceName = Repository\DocumentAnalysis::class;

    protected $extraRepos = ['Application', Repository\Document::class];

    public function __construct(
        protected readonly EventBridge $eventBridgeService,
        protected readonly AnalysisTokenGenerator $tokenGenerator
    ) {
    }

    /**
     * @param AnalyseDocumentCommandInterface $command
     */
    #[\Override]
    public function handleCommand(CommandInterface $command)
    {
        if (!$command instanceof AnalyseDocumentCommandInterface) {
            throw new RuntimeException(sprintf('%s cannot handle %s', static::class, $command::class));
        }

        /** @var Entity\Application\Application $application */
        $application = $this->getRepo('Application')->fetchById($command->getApplication());

        $documents = $this->resolveDocuments($application, $command->getDocument());

        if ($documents === []) {
            $this->result->addMessage($this->getNoDocumentsMessage($application));

            return $this->result;
        }

        foreach ($this->filterAlreadyAnalysed($documents) as $document) {
            $this->submitDocument($application, $document);
        }

        return $this->result;
    }

    /** Row first, then event: if the emit throws, the sweeper owns the row from here. */
    protected function submitDocument(
        Entity\Application\Application $application,
        Entity\Doc\Document $document
    ): void {
        [$binaryToken, $tokenString] = $this->tokenGenerator->generate();

        /** @var Repository\DocumentAnalysis $analysisRepo */
        $analysisRepo = $this->getRepo();
        $analysis = $analysisRepo->createPending($binaryToken, $application, $document);

        $this->result->addId('documentAnalysis', $analysis->getId(), true);

        try {
            $this->eventBridgeService->emit($this->createEvent($application, $document, $tokenString));
            $this->result->addMessage(sprintf('Analysis requested for document %d', $document->getId()));
        } catch (\Exception $e) {
            // Deliberate: the row stays PENDING and the sweeper resolves it.
            Logger::err(
                'Failed to emit IDP analysis event; row left PENDING for the sweeper',
                [
                    'application_id' => $application->getId(),
                    'document_id' => $document->getId(),
                    'analysis_token' => $tokenString,
                    'exception' => [
                        'class' => $e::class,
                        'message' => $e->getMessage(),
                    ],
                ]
            );

            $this->result->addMessage(
                sprintf('Analysis event emit failed for document %d; left pending', $document->getId())
            );
        }
    }

    /**
     * Skip documents that already have an in-flight or recently successful analysis.
     *
     * @param Entity\Doc\Document[] $documents
     *
     * @return Entity\Doc\Document[]
     */
    protected function filterAlreadyAnalysed(array $documents): array
    {
        $documentIds = array_map(static fn(Entity\Doc\Document $d): int => (int)$d->getId(), $documents);

        /** @var Repository\DocumentAnalysis $analysisRepo */
        $analysisRepo = $this->getRepo();
        $skip = $analysisRepo->fetchDocumentIdsWithActiveAnalysis($documentIds, $this->getSuccessWindowStart());

        if ($skip === []) {
            return $documents;
        }

        $skipLookup = array_flip($skip);
        $remaining = [];

        foreach ($documents as $document) {
            if (isset($skipLookup[(int)$document->getId()])) {
                $this->result->addMessage(
                    sprintf('Document %d already has an active analysis; skipped', $document->getId())
                );
                continue;
            }

            $remaining[] = $document;
        }

        return $remaining;
    }

    /**
     * The explicit document if the command named one, else every evidence document.
     *
     * @return Entity\Doc\Document[]
     */
    protected function resolveDocuments(
        Entity\Application\Application $application,
        ?int $documentId
    ): array {
        if ($documentId !== null) {
            /** @var Entity\Doc\Document $document */
            $document = $this->getRepo(Repository\Document::class)->fetchById($documentId);

            return [$document];
        }

        return $this->findDocumentsForApplication($application);
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

    /** Start of the window inside which an existing SUCCESS suppresses re-analysis. */
    protected function getSuccessWindowStart(): \DateTimeInterface
    {
        $hours = (int)($this->getConfig()['idp']['dedupe_success_window_hours'] ?? 24);

        return new \DateTimeImmutable(sprintf('-%d hours', max(0, $hours)));
    }

    /**
     * @return Entity\Doc\Document[]
     */
    abstract protected function findDocumentsForApplication(Entity\Application\Application $application): array;

    abstract protected function getNoDocumentsMessage(Entity\Application\Application $application): string;

    abstract protected function createEvent(
        Entity\Application\Application $application,
        Entity\Doc\Document $document,
        string $analysisToken
    ): EventInterface;
}
