<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Domain\CommandHandler\Permits;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Dvsa\Olcs\Api\Rbac\IdentityProviderInterface;
use Dvsa\Olcs\Cli\Domain\Command\Permits\UploadScoringLog as UploadScoringLogCommand;
use Dvsa\Olcs\Cli\Domain\CommandHandler\Permits\UploadScoringLog as UploadScoringLogHandler;
use Dvsa\Olcs\Transfer\Command\Document\Upload;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 * Upload scoring log test
 *
 * @author Jason de Jonge <jason.de-jonge@capgemini.co.uk>
 */
class UploadScoringLogTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UploadScoringLogHandler();

        parent::setUp();
    }

    /**
     * tests handleCommand
     */
    public function testHandleCommand(): void
    {
        $logContent = 'test';
        $result1 = new Result();
        $result1->addMessage('Document created');

        $this->expectedSideEffect(
            Upload::class,
            [
              'content' => base64_encode($logContent),
              'category' => Category::CATEGORY_PERMITS,
              'subCategory' => SubCategory::REPORT_SUB_CATEGORY_PERMITS,
              'filename' => 'Permit-Scoring-Log.log',
              'description' => 'Scoring Log File ' . date('Y-m-d H:i'),
              'user' => IdentityProviderInterface::SYSTEM_USER,
            ],
            $result1
        );

        $this->sut->handleCommand(
            UploadScoringLogCommand::create(['logContent' => $logContent])
        );
    }
}
