<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Document;

use Dvsa\Olcs\Api\Domain\Command\Queue\Create;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Repository\DocumentToDelete;
use Dvsa\Olcs\Api\Entity\Queue\Queue;
use Dvsa\Olcs\Api\Service\File\FileUploaderInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Repository\Queue as QueueRepo;
use Psr\Container\ContainerInterface;

/**
 * Remove deleted documents from the file store
 */
final class RemoveDeletedDocuments extends AbstractCommandHandler implements TransactionedInterface
{
    public const int NUMBER_DOCS_TO_REMOVE = 100;

    protected $repoServiceName = 'DocumentToDelete';

    protected $extraRepos = ['SystemParameter','Queue'];

    /**
     * @var FileUploaderInterface
     */
    private $contentStoreService;

    /**
     * Get the Content store service
     *
     * @return FileUploaderInterface
     */
    public function getContentStoreService()
    {
        return $this->contentStoreService;
    }

    /**
     * Set the Content store service
     *
     * @param FileUploaderInterface $contentStoreService ContentStoreService to set
     *
     * @return void
     */
    public function setContentStoreService(FileUploaderInterface $contentStoreService)
    {
        $this->contentStoreService = $contentStoreService;
    }

    /**
     * Deletes a document and optionally triggers side effect of deleting the associated EBSR submission
     *
     * @param CommandInterface $command command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $successCount = 0;
        $notFoundCount = 0;
        $errorCount = 0;

        if ($this->getRepo('SystemParameter')->getDisableDataRetentionDocumentDelete()) {
            $this->result->addMessage('Removing deleted documents is disabled by system parameter');
            return $this->result;
        }

        /** @var DocumentToDelete $repo */
        $repo = $this->getRepo();
        $documentsToDelete = $repo->fetchListOfDocumentToDelete(self::NUMBER_DOCS_TO_REMOVE);

        /** @var \Dvsa\Olcs\Api\Entity\Doc\DocumentToDelete $documentToDelete */
        foreach ($documentsToDelete as $documentToDelete) {
            $response = $this->getContentStoreService()->remove($documentToDelete->getDocumentStoreId());

            if ($response->isOk()) {
                // Document was successfully deleted
                $repo->delete($documentToDelete);
                $successCount++;
            } elseif ($response->isNotFound()) {
                // Document not found on content store
                $repo->delete($documentToDelete);
                $notFoundCount++;
            } else {
                // An error occurred trying to delete the document
                $documentToDelete->markAsFailed();
                $repo->save($documentToDelete);
                $this->result->addMessage(
                    sprintf(
                        'Document delete failed. DocumnetStoreId = \'%s\', code = %d',
                        $documentToDelete->getDocumentStoreId(),
                        $response->getStatusCode()
                    )
                );
                $errorCount++;
            }
        }

        $this->result->addMessage(
            sprintf(
                'Remove documents : %d success, %d not found, %d errors',
                $successCount,
                $notFoundCount,
                $errorCount
            )
        );

        $this->maybeCreateQueueItem($repo);

        return $this->result;
    }

    private function maybeCreateQueueItem(DocumentToDelete $repo)
    {
        /** @var QueueRepo $queue */
        $queue = $this->getRepo('Queue');
        $moreDocumentsToDelete = $repo->fetchListOfDocumentToDeleteIncludingPostponed(1);

        if (count($moreDocumentsToDelete) == 0) {
            return;
        }

        /** @var Queue $nextDocumentQueueItem */
        $nextDocumentQueueItem = $queue->fetchNextItemIncludingPostponed([Queue::TYPE_REMOVE_DELETED_DOCUMENTS]);

        /** @var \Dvsa\Olcs\Api\Entity\Doc\DocumentToDelete $documentToDelete */
        $documentToDelete = $moreDocumentsToDelete[0];

        if (
            $nextDocumentQueueItem !== null &&
            ($nextDocumentQueueItem->getProcessAfterDate() === null ||
                $nextDocumentQueueItem->getProcessAfterDate() < $documentToDelete->getProcessAfterDate())
        ) {
            return;
        }

        $commandParameters = [
            'type' => Queue::TYPE_REMOVE_DELETED_DOCUMENTS,
            'status' => Queue::STATUS_QUEUED
        ];

        if ($documentToDelete->getProcessAfterDate() !== null) {
            $commandParameters['processAfterDate'] = $documentToDelete->getProcessAfterDate()->format('Y-m-d H:i:s');
        }

        $command = Create::create($commandParameters);
        $this->handleSideEffect($command);
    }
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $fullContainer = $container;

        $this->setContentStoreService($container->get('FileUploader'));
        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}
