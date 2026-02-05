<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\CommunityLic;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\CommunityLic\BulkReprint as BulkReprintCmd;
use Dvsa\Olcs\Api\Domain\Command\CommunityLic\ValidatingReprintCaller as ValidatingReprintCallerCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\UploaderAwareInterface;
use Dvsa\Olcs\Api\Domain\UploaderAwareTrait;
use Dvsa\Olcs\DocumentShare\Data\Object\File as ContentStoreFile;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Exception;
use RuntimeException;

/**
 * Bulk reprint community licences
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
final class BulkReprint extends AbstractCommandHandler implements UploaderAwareInterface
{
    use UploaderAwareTrait;

    public const int EXPECTED_ITEMS_IN_ROW = 3;
    public const int MAX_LINE_COUNT = 5000;

    protected $repoServiceName = 'Document';

    /** @var array */
    private $licences;

    /** @var int */
    private $lineNumber;

    /** @var int */
    private $userId;

    /**
     * Handle command
     *
     * @param BulkReprintCmd|CommandInterface $command command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var ContentStoreFile $file */
        $file = $this->uploader->download(
            $command->getDocumentIdentifier()
        );

        $fp = fopen('php://memory', 'r+');
        fputs($fp, $file->getContent());

        $lineCount = $this->getLineCount($fp);
        if ($lineCount <= self::MAX_LINE_COUNT) {
            $this->userId = $command->getUser();
            $this->processFile($fp);
        } else {
            $this->result->addMessage(
                sprintf(
                    'Line count of %d exceeds permitted maximum of %d - file not processed',
                    $lineCount,
                    self::MAX_LINE_COUNT
                )
            );
        }

        return $this->result;
    }

    /**
     * Get the number of lines in the specified file resource
     *
     * @param resource $fp
     *
     * @return int
     */
    private function getLineCount($fp)
    {
        rewind($fp);
        $lineCount = 0;

        while (!feof($fp)) {
            fgets($fp);
            $lineCount++;
        }

        return $lineCount;
    }

    /**
     * Attempt to reprint multiple community licences based upon the csv content in the supplied file resource
     *
     * @param resource $fp
     */
    private function processFile($fp)
    {
        $this->lineNumber = 1;
        $this->licences = [];

        rewind($fp);
        while (($row = fgetcsv($fp)) !== false) {
            $this->processRow($row);
            $this->lineNumber++;
        }

        foreach ($this->licences as $licenceId => $communityLicences) {
            $this->result->merge(
                $this->handleSideEffect(
                    ValidatingReprintCallerCmd::create(
                        [
                            'communityLicences' => $communityLicences,
                            'licence' => $licenceId,
                            'user' => $this->userId,
                        ]
                    )
                )
            );
        }

        $this->result->addMessage('Processing completed successfully');
    }

    /**
     * Attempt to reprint the community licence corresponding to the specified row values
     */
    private function processRow(array $row)
    {
        $itemsInRow = count($row);
        if ($itemsInRow != self::EXPECTED_ITEMS_IN_ROW) {
            $this->result->addMessage(
                sprintf(
                    'Error on line %d: expected %d items in row, found %d',
                    $this->lineNumber,
                    self::EXPECTED_ITEMS_IN_ROW,
                    $itemsInRow
                )
            );

            return;
        }

        [$communityLicenceId, $communityLicenceIssueNo, $licenceId] = $row;

        if (!array_key_exists($licenceId, $this->licences)) {
            $this->licences[$licenceId] = [];
        }

        $this->licences[$licenceId][] = [
            'communityLicenceId' => $communityLicenceId,
            'communityLicenceIssueNo' => $communityLicenceIssueNo
        ];
    }
}
