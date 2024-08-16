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
final class UploadScoringLog extends ScoringCommandHandler
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
        $this->profileMessage('upload scoring log...');

        $result = new Result();

        $content = $command->getLogContent();

        $data = [
            'content' => base64_encode($content),
            'category' => Category::CATEGORY_PERMITS,
            'subCategory' => SubCategory::REPORT_SUB_CATEGORY_PERMITS,
            'filename' => 'Permit-Scoring-Log.log',
            'description' => 'Scoring Log File ' . date('Y-m-d H:i'),
            'user' => IdentityProviderInterface::SYSTEM_USER,
        ];

        $this->handleSideEffect(
            UploadCmd::create($data)
        );

        $result->addMessage('Log file successfully uploaded.');

        return $result;
    }
}
