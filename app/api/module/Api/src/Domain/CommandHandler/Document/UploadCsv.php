<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Document;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Document\UploadCsv as UploadCsvCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Rbac\IdentityProviderInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Document\Upload as UploadCmd;

final class UploadCsv extends AbstractCommandHandler
{
    public const string CONFIRM_MSG = 'CSV containing %d rows was uploaded';
    public const string EMPTY_MSG = 'No data found. Creating empty file';

    /**
     * Handle command
     *
     * @param CommandInterface|UploadCsvCmd $command command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command): Result
    {
        $csvContent = $command->getCsvContent();
        $numRows = count($csvContent);
        $fileDescription = $command->getFileDescription();
        $fileName = $fileDescription . '.csv';
        $userId = IdentityProviderInterface::SYSTEM_USER;
        $userFromCommand = $command->getUser();

        //default to the system user
        if ($userFromCommand) {
            $userId = $userFromCommand;
        }

        //  create csv file in memory
        $fh = fopen('php://temp', 'w');

        if (!empty($csvContent)) {
            //use this to get our header row
            fputcsv($fh, array_keys($csvContent[0]));

            //create rows
            foreach ($csvContent as $dataRow) {
                fputcsv($fh, $dataRow);
            }
        } else {
            //  no results, create empty file
            fputcsv($fh, ['No Results']);
            $this->result->addMessage(self::EMPTY_MSG);
        }

        unset($csvContent);

        rewind($fh);
        $content = stream_get_contents($fh);
        fclose($fh);

        $data = [
            'content' => base64_encode($content),
            'category' => $command->getCategory(),
            'subCategory' => $command->getSubCategory(),
            'filename' => $fileName,
            'description' => $fileDescription,
            'user' => $userId,
        ];

        unset($content);

        $this->result->merge(
            $this->handleSideEffect(
                UploadCmd::create($data)
            )
        );

        if ($numRows > 0) {
            $this->result->addMessage(sprintf(self::CONFIRM_MSG, $numRows));
        }

        return $this->result;
    }
}
