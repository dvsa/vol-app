<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Document;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\CacheAwareInterface;
use Dvsa\Olcs\Api\Domain\CacheAwareTrait;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Doc\Document;
use Dvsa\Olcs\Api\Domain\Command\Document\CreateDocumentSpecific as Cmd;
use Dvsa\Olcs\Api\Domain\Command\Bus\Ebsr\CreateSubmission as CreateEbsrSubmissionCmd;
use Dvsa\Olcs\Transfer\Command\Document\UpdateDocumentLinks as UpdateDocumentLinksCommand;
use Dvsa\Olcs\Transfer\Service\CacheEncryption;

/**
 * Create Document
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class CreateDocumentSpecific extends AbstractCommandHandler implements AuthAwareInterface, CacheAwareInterface
{
    use CacheAwareTrait;
    use AuthAwareTrait;

    /**
     * @var string
     */
    protected $repoServiceName = 'Document';

    /**
     * Handle command
     *
     * @param CommandInterface|Cmd $command the command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        $document = $this->createDocumentEntity($command);

        $this->getRepo()->save($document);

        $data = $command->getArrayCopy();
        $data['id'] = $document->getId();

        $this->updateCorrelationStore($command, $document);

        $result->merge($this->handleSideEffect(UpdateDocumentLinksCommand::create($data)));

        //if the document is an EBSR pack, create a corresponding EBSR submission
        if ($data['isEbsrPack']) {
            $result->merge($this->handleSideEffect(CreateEbsrSubmissionCmd::create(['document' => $data['id']])));
        }

        $result->addId('document', $document->getId());
        $result->addMessage('Document created');

        return $result;
    }

    /**
     * Create a document entity
     *
     * @param Cmd $command the command
     *
     * @return Document
     */
    private function createDocumentEntity(Cmd $command)
    {
        $document = new Document($command->getIdentifier());

        $this->categoriseDocument($document, $command);
        $this->setDocumentDetails($document, $command);
        $this->setDocumentFlags($document, $command);

        return $document;
    }

    /**
     * Set document flags
     *
     * @param Document $document document entity
     * @param Cmd      $command  the command
     *
     * @return void
     */
    private function setDocumentFlags(Document $document, Cmd $command)
    {
        $document->setIsExternal($command->getIsExternal());
        $document->setIsScan($command->getIsScan());
        $document->setIsPostSubmissionUpload($command->getIsPostSubmissionUpload());
    }

    /**
     * Set document details
     *
     * @param Document $document document entity
     * @param Cmd      $command  the command
     *
     * @return void
     */
    private function setDocumentDetails(Document $document, Cmd $command)
    {
        $document->setFilename($command->getFilename());
        $document->setSize($command->getSize());
        $document->setDescription($command->getDescription());

        if ($command->getIssuedDate() !== null) {
            $document->setIssuedDate(new \DateTime($command->getIssuedDate()));
        }

        if ($command->getMetadata() !== null) {
            $document->setMetadata($command->getMetadata());
        }
    }

    /**
     * Set document flags
     *
     * @param Document $document document entity
     * @param Cmd      $command  the command
     *
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     * @return void
     */
    private function categoriseDocument(Document $document, Cmd $command)
    {
        if ($command->getCategory() != null) {
            $document->setCategory($this->getRepo()->getCategoryReference($command->getCategory()));
        }

        if ($command->getSubCategory() != null) {
            $document->setSubCategory($this->getRepo()->getSubCategoryReference($command->getSubCategory()));
        }
    }

    private function updateCorrelationStore(CommandInterface $command, Document $document): void
    {
        if ($command->getCorrelationId() === null) {
            return;
        }

        $ids = $this->getCache()->getCustomItem(CacheEncryption::GENERIC_STORAGE_IDENTIFIER, $command->getCorrelationId()) ?: [];
        $ids[] = $document->getId();
        $this->getCache()->setCustomItem(CacheEncryption::GENERIC_STORAGE_IDENTIFIER, $ids, $command->getCorrelationId());
    }
}
