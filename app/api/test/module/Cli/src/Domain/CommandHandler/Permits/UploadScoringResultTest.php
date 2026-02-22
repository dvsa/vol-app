<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Domain\CommandHandler\Permits;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Dvsa\Olcs\Api\Rbac\IdentityProviderInterface;
use Dvsa\Olcs\Cli\Domain\Command\Permits\UploadScoringResult as UploadScoringResultCommand;
use Dvsa\Olcs\Cli\Domain\CommandHandler\Permits\UploadScoringResult as UploadScoringResultHandler;
use Dvsa\Olcs\Transfer\Command\Document\Upload;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 * Upload scoring result test
 *
 * @author Jason de Jonge <jason.de-jonge@capgemini.co.uk>
 */
class UploadScoringResultTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UploadScoringResultHandler();

        parent::setUp();
    }

    /**
     * tests handleCommand
     */
    public function testHandleCommand(): void
    {
        $csvData = [
            0 => [
                'test',
            ]
        ];
        $fileDesc = 'TEST';
        $csvContent = 'MAp0ZXN0Cg=='; //this is the output of base64_encode($csvData)
        $result1 = new Result();

        $this->expectedSideEffect(
            Upload::class,
            [
              'content' => $csvContent,
              'category' => Category::CATEGORY_PERMITS,
              'subCategory' => SubCategory::REPORT_SUB_CATEGORY_PERMITS,
              'filename' => 'Permit-Scoring-Report.csv',
              'description' => $fileDesc . ' ' . date('Y-m-d H:i'),
              'user' => IdentityProviderInterface::SYSTEM_USER,
            ],
            $result1
        );

        $result = $this->sut->handleCommand(
            UploadScoringResultCommand::create(['csvContent' => $csvData, 'fileDescription' => $fileDesc])
        );

        $expectedMessages = ['Scoring results file successfully uploaded'];

        $this->assertEquals(
            $expectedMessages,
            $result->getMessages()
        );
    }

    public function testHandleCommandNoContent(): void
    {
        $csvData = [];
        $fileDesc = 'TEST';
        $csvContent = 'Ik5vIFJlc3VsdHMiCg=='; //this is the output of base64_encode($csvData)
        $result1 = new Result();

        $this->expectedSideEffect(
            Upload::class,
            [
              'content' => $csvContent,
              'category' => Category::CATEGORY_PERMITS,
              'subCategory' => SubCategory::REPORT_SUB_CATEGORY_PERMITS,
              'filename' => 'Permit-Scoring-Report.csv',
              'description' => $fileDesc . ' ' . date('Y-m-d H:i'),
              'user' => IdentityProviderInterface::SYSTEM_USER,
            ],
            $result1
        );

        $result = $this->sut->handleCommand(
            UploadScoringResultCommand::create(['csvContent' => $csvData, 'fileDescription' => $fileDesc])
        );

        $expectedMessages = [
            'No scoring results passed. Creating empty report file',
            'Scoring results file successfully uploaded'
        ];

        $this->assertEquals(
            $expectedMessages,
            $result->getMessages()
        );
    }
}
