<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Permits;

use Dvsa\Olcs\Api\Domain\Command\Permits\PostScoringEmail as PostScoringEmailCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\UploaderAwareInterface;
use Dvsa\Olcs\Api\Domain\UploaderAwareTrait;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEcmtApsgPostScoring;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\QueueAwareTrait;

/**
 * Post scoring email
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
final class PostScoringEmail extends AbstractCommandHandler implements UploaderAwareInterface
{
    use QueueAwareTrait;
    use UploaderAwareTrait;

    protected $repoServiceName = 'IrhpApplication';

    /**
     * Handle command
     *
     * @param PostScoringEmailCmd|CommandInterface $command command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $file = $this->uploader->download(
            $command->getDocumentIdentifier()
        );

        $fp = fopen('php://memory', 'r+');
        fputs($fp, $file->getContent());

        $this->processFile($fp);

        return $this->result;
    }

    /**
     * Send an email for each qualifying application specified in the CSV
     *
     * @param resource $fp
     */
    private function processFile($fp)
    {
        rewind($fp);
        $line = 1;
        while (($row = fgetcsv($fp)) !== false) {
            $message = $this->processRow($row);

            $this->result->addMessage(
                sprintf('Line %d: %s', $line, $message)
            );

            $line++;
        }
        $this->result->addMessage('All lines processed');
    }

    /**
     * Attempt to process a row from the CSV and return a human-readable message indicating the outcome
     *
     *
     * @return string
     */
    private function processRow(array $row)
    {
        if (count($row) != 1) {
            return 'Ignored due to multiple columns';
        }

        $irhpApplicationId = $row[0];

        try {
            $irhpApplication = $this->getRepo()->fetchById($irhpApplicationId);
        } catch (NotFoundException) {
            $irhpApplication = null;
        }

        if (!is_object($irhpApplication)) {
            return sprintf(
                'Ignored due to application id %s not being found',
                $irhpApplicationId
            );
        }

        if (!$irhpApplication->hasStateRequiredForPostScoringEmail()) {
            return sprintf(
                'Ignored due to application id %s not being in the correct state for post scoring email',
                $irhpApplicationId
            );
        }

        $licence = $irhpApplication->getLicence();
        if (!$licence->hasStatusRequiredForPostScoringEmail()) {
            return sprintf(
                'Ignored due to the licence associated with application id %s not ' .
                'being in the correct state for post scoring email',
                $irhpApplicationId
            );
        }

        $this->handleSideEffect(
            $this->emailQueue(
                SendEcmtApsgPostScoring::class,
                [ 'id' => $irhpApplicationId ],
                $irhpApplicationId
            )
        );

        return sprintf(
            'Email sent for application id %d',
            $irhpApplicationId
        );
    }
}
