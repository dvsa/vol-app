<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Document;

use Dvsa\Olcs\Api\Domain\Command as DomainCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\UploaderAwareInterface;
use Dvsa\Olcs\Api\Domain\UploaderAwareTrait;
use Dvsa\Olcs\Api\Service\Document\NamingServiceAwareInterface;
use Dvsa\Olcs\Api\Service\File\MimeNotAllowedException;
use Dvsa\Olcs\DocumentShare\Data\Object\File as DsFile;
use Dvsa\Olcs\Transfer\Command as TransferCmd;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Utils\Helper\FileHelper;

/**
 * Upload
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class Upload extends AbstractCommandHandler implements
    TransactionedInterface,
    UploaderAwareInterface,
    NamingServiceAwareInterface
{
    use UploaderAwareTrait;

    public const string ERR_MIME = 'ERR_MIME';
    public const string ERR_EBSR_MIME = 'ERR_EBSR_MIME';

    protected $repoServiceName = 'Document';

    /**
     * Execute command
     *
     * @param TransferCmd\Document\Upload $command Command
     *
     * @return Result
     * @throws ValidationException
     */
    public function handleCommand(CommandInterface $command)
    {
        $additionalCopy = $command->getAdditionalCopy();

        if (!empty($additionalCopy)) {
            $additionalCommand = $this->getAdditionalCommand($command);
        }

        $identifier = $this->determineIdentifier($command);

        $file = $this->uploadFile($command, $identifier);

        if (!empty($additionalCopy)) {
            $additionalIdentifier = $this->determineIdentifier($command);
            $additionalFile = $this->uploadFile($additionalCommand, $additionalIdentifier);
        }

        if (!$command->getShouldUploadOnly()) {
            $this->result->merge($this->createDocument($command, $file, $identifier));
            if (!empty($additionalCopy)) {
                $this->result->merge(
                    $this->createDocument($additionalCommand, $additionalFile, $additionalIdentifier)
                );
            }
        }

        return $this->result;
    }

    /**
     * Define file name(path)
     *
     * @param TransferCmd\Document\Upload $command Upload command
     *
     * @return string
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    private function determineIdentifier(TransferCmd\Document\Upload $command)
    {
        $description = $this->getDescriptionFromCommand($command);

        $extension = FileHelper::getExtension($command->getFilename());

        $category = null;
        $subCategory = null;
        $entity = $this->determineEntityFromCommand($command->getArrayCopy());

        if ($command->getCategory() !== null) {
            $category = $this->getRepo()->getCategoryReference($command->getCategory());
        }

        if ($command->getSubCategory() !== null) {
            $subCategory = $this->getRepo()->getSubCategoryReference($command->getSubCategory());
        }

        return $this->getNamingService()->generateName($description, $extension, $category, $subCategory, $entity);
    }

    /**
     * Upload file to Document storage
     *
     * @param TransferCmd\Document\Upload $command    Upload Command
     * @param string                      $identifier File name (path)
     *
     * @return DsFile
     * @throws ValidationException
     */
    protected function uploadFile(TransferCmd\Document\Upload $command, $identifier)
    {
        $content = $command->getContent();

        $dsFile = new DsFile();
        if (!empty($content['tmp_name'])) {
            $dsFile->setContentFromStream($content['tmp_name']);

            if ('application/octet-stream' === $dsFile->getMimeType()) {
                $dsFile->setMimeType($content['type']);
            }
        } else {
            $dsFile->setContent(base64_decode($content));
        }

        if ($command->getIsEbsrPack() && $dsFile->getMimeType() !== 'application/zip') {
            throw new ValidationException([self::ERR_EBSR_MIME => self::ERR_EBSR_MIME]);
        }

        try {
            $file = $this->getUploader()
                ->upload($identifier, $dsFile);

            $this->result->addMessage('File uploaded');
            $this->result->addId('identifier', $file->getIdentifier());

            return $file;
        } catch (MimeNotAllowedException) {
            throw new ValidationException([self::ERR_MIME => self::ERR_MIME]);
        } catch (\Exception $e) {
            unset($dsFile);

            throw $e;
        }
    }

    /**
     * Create document
     *
     * @param TransferCmd\Document\Upload $command    Upload command
     * @param DsFile                      $file       File
     * @param string                      $identifier File name (path)
     *
     * @return Result
     */
    protected function createDocument(TransferCmd\Document\Upload $command, DsFile $file, $identifier)
    {
        $data = $command->getArrayCopy();
        unset($data['content']);

        $data['identifier'] = $file->getIdentifier();
        $data['size'] = $file->getSize();
        $data['filename'] = $identifier;
        $data['description'] = $this->getDescriptionFromCommand($command);
        $data['user'] = $command->getUser();
        $data['correlationId'] = $command->getCorrelationId();

        if ($data['isExternal'] === null) {
            $cmd = DomainCmd\Document\CreateDocument::create($data);
        } else {
            $cmd = DomainCmd\Document\CreateDocumentSpecific::create($data);
        }

        return $this->handleSideEffect($cmd);
    }

    /**
     * Get description from command data
     *
     * @param TransferCmd\Document\Upload $command Upload command
     *
     * @return string
     */
    protected function getDescriptionFromCommand(TransferCmd\Document\Upload $command)
    {
        $description = $command->getDescription();

        if ($description !== null) {
            return $description;
        }

        $filename = $command->getFilename();

        $parts = explode('.', (string) $filename);

        array_pop($parts);

        return implode('', $parts);
    }

    /**
     * Get additional command
     *
     * @param TransferCmd\Document\Upload $command Upload command
     *
     * @return TransferCmd\Document\Upload
     */
    protected function getAdditionalCommand($command)
    {
        // sometimes we need to upload the file twice and to attach it
        // to different entities, here we are preparing a new command
        // which should be exactly the same as original, apart from the target entities

        // prepare new command
        $additionalCommand = clone $command;

        // clear all target entities for the new command
        $propertiesToClear = [
            'application',
            'licence',
            'transportManager',
            'surrender',
            'case',
            'busReg',
            'irhpApplication'
        ];

        foreach ($propertiesToClear as $property) {
            $method = 'set' . ucfirst($property);
            $additionalCommand->$method(null);
        }

        // copy necessary target entities to the new command and clear them off from original command
        $additionalEntities = $command->getAdditionalEntities();
        foreach ($additionalEntities as $additionalEntity) {
            $getMethod = 'get' . ucfirst((string) $additionalEntity);
            $setMethod = 'set' . ucfirst((string) $additionalEntity);
            $additionalCommand->$setMethod($command->$getMethod());
            $command->$setMethod(null);
        }
        return $additionalCommand;
    }
}
