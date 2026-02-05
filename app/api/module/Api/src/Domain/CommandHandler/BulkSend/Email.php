<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\BulkSend;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\BulkSend\Email as EmailCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\EmailAwareTrait;
use Dvsa\Olcs\Api\Domain\UploaderAwareInterface;
use Dvsa\Olcs\Api\Domain\UploaderAwareTrait;
use Dvsa\Olcs\DocumentShare\Data\Object\File as ContentStoreFile;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\BulkSend\ProcessEmail;

/**
 * Bulk send emails
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
final class Email extends AbstractCommandHandler implements UploaderAwareInterface
{
    use EmailAwareTrait;
    use UploaderAwareTrait;

    public const int EXPECTED_ITEMS_IN_ROW = 1;

    /** @var array */
    private $licenceIds  = [];

    private $templateName;

    /**
     * Handle command
     *
     * @param EmailCmd|CommandInterface $command command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var ContentStoreFile $file */
        $file = $this->uploader->download(
            $command->getDocumentIdentifier()
        );

        $this->templateName = $command->getTemplateName();

        $fp = fopen('php://memory', 'r+');
        fputs($fp, $file->getContent());

        $this->processFile($fp);

        return $this->result;
    }

    /**
     * Prepare emails for each licence specified in the CSV
     *
     * @param resource $fp
     */
    private function processFile($fp)
    {
        rewind($fp);
        while (($row = fgetcsv($fp)) !== false) {
            $licenceId = $row[0];
            if ($licenceId != 'licence_id' && !in_array($licenceId, $this->licenceIds)) {
                $this->licenceIds[] = $licenceId;
                $this->result->merge(
                    $this->handleSideEffect(ProcessEmail::create(['id' => $licenceId, 'templateName' => $this->templateName]))
                );
            }
        }

        $this->result->addMessage('Processing completed successfully');
    }
}
