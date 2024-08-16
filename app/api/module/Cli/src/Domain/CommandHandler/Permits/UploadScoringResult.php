<?php

namespace Dvsa\Olcs\Cli\Domain\CommandHandler\Permits;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Rbac\IdentityProviderInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Document\Upload as UploadCmd;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SubCategory;

/**
 * Upload the log output for the permit scoring
 * batch process
 */
final class UploadScoringResult extends ScoringCommandHandler
{
    protected $repoServiceName = 'IrhpCandidatePermit';

    /**
    * Handle command
    *
    * @param CommandInterface $command command
    *
    * @return Result
    */
    public function handleCommand(CommandInterface $command)
    {
        $this->profileMessage('upload scoring result...');

        $result = new Result();

        $csvContent = $command->getCsvContent();
        $fileDescription = $command->getFileDescription();

        //  create csv file in memory
        $fh = fopen('php://temp', 'w');

        if (!empty(($csvContent))) {
            fputcsv($fh, array_keys($csvContent[0])); //row of headers

            foreach ($csvContent as $dataRow) { //insert content
                fputcsv($fh, $dataRow);
            }
        } else {
            //  no results, still need to put something inside .csv file so it is generated
            fputcsv($fh, ['No Results']);
            $result->addMessage('No scoring results passed. Creating empty report file');
        }

        rewind($fh);
        $content = stream_get_contents($fh);

        fclose($fh);

        $data = [
            'content' => base64_encode($content),
            'category' => Category::CATEGORY_PERMITS,
            'subCategory' => SubCategory::REPORT_SUB_CATEGORY_PERMITS,
            'filename' => 'Permit-Scoring-Report.csv',
            'description' => $fileDescription . ' ' . date('Y-m-d H:i'),
            'user' => IdentityProviderInterface::SYSTEM_USER,
        ];

        unset($content);

        $this->handleSideEffect(
            UploadCmd::create($data)
        );

        $result->addMessage('Scoring results file successfully uploaded');

        return $result;
    }
}
