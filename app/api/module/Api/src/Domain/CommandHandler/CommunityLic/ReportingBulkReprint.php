<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\CommunityLic;

use Dvsa\Olcs\Api\Domain\Command\CommunityLic\BulkReprint as BulkReprintCmd;
use Dvsa\Olcs\Api\Domain\Command\CommunityLic\ReportingBulkReprint as ReportingBulkReprintCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\UploaderAwareInterface;
use Dvsa\Olcs\Api\Domain\UploaderAwareTrait;
use Dvsa\Olcs\DocumentShare\Data\Object\File as ContentStoreFile;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Reporting of bulk reprint community licences
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
final class ReportingBulkReprint extends AbstractCommandHandler implements UploaderAwareInterface
{
    use UploaderAwareTrait;

    public const string UPLOAD_PATH = 'documents/Report/';
    public const string FILENAME_EXTENSION = 'log';

    /**
     * Handle command
     *
     * @param ReportingBulkReprintCmd|CommandInterface $command command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $documentIdentifier = $command->getDocumentIdentifier();

        $cmd = BulkReprintCmd::create(
            [
                'documentIdentifier' => $documentIdentifier,
                'user' => $command->getUser(),
            ]
        );
        $bulkReprintResult = $this->handleSideEffect($cmd);

        $documentIdentifierComponents = explode('/', $documentIdentifier);
        $filenameComponents = explode('.', end($documentIdentifierComponents));
        $filenameComponents[count($filenameComponents) - 1] = self::FILENAME_EXTENSION;
        $uploadPath = self::UPLOAD_PATH . implode('.', $filenameComponents);

        $content = implode(
            "\r\n",
            $bulkReprintResult->getMessages()
        );

        $file = new ContentStoreFile();
        $file->setContent($content);
        $this->getUploader()->upload($uploadPath, $file);

        $this->result->addMessage('File successfully uploaded to path ' . $uploadPath);

        return $this->result;
    }
}
