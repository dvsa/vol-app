<?php

/**
 * DocTemplate shared methods trait
 *
 * @author Andy Newton <andy@vitri.ltd>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\DocTemplate;

use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Service\File\MimeNotAllowedException;
use Dvsa\Olcs\Transfer\Command as TransferCmd;
use Dvsa\Olcs\Api\Domain\Command as DomainCmd;
use Dvsa\Olcs\DocumentShare\Data\Object\File as DsFile;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

trait DocTemplateTrait
{
    /**
     * Create document
     *
     * @param CommandInterface $command Upload command
     * @param DsFile $file File
     * @param string $identifier File name (path)
     *
     * @return Result
     */
    protected function createDocument(CommandInterface $command, DsFile $file, $identifier)
    {
        $data = $command->getArrayCopy();
        unset($data['content']);
        $data['identifier'] = $file->getIdentifier();
        $data['size'] = $file->getSize();
        $data['filename'] = $identifier;
        $data['description'] = $command->getDescription();
        $data['isExternal'] = 0;
        $data['user'] = $this->getCurrentUser();

        $cmd = DomainCmd\Document\CreateDocumentSpecific::create($data);

        return $this->handleSideEffect($cmd);
    }

    /**
     * Upload file to Document template storage
     *
     * @param CommandInterface $command Upload Command
     * @param string $identifier File name (path)
     *
     * @return DsFile
     * @throws ValidationException
     */
    protected function uploadFile(CommandInterface $command, $identifier)
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
}
