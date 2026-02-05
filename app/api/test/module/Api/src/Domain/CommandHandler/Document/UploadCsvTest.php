<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Document;

use Dvsa\Olcs\Api\Domain\Command\Document\UploadCsv as UploadCsvCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Document\UploadCsv as UploadCsvHandler;
use Dvsa\Olcs\Api\Rbac\IdentityProviderInterface;
use Dvsa\Olcs\Transfer\Command\Document\Upload;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 * @see UploadCsvHandler
 */
class UploadCsvTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UploadCsvHandler();
        parent::setUp();
    }

    public function testHandleCommandNoResults(): void
    {
        $csvContent = [];
        $encodedContent = 'Ik5vIFJlc3VsdHMiCg==';
        $category = 111;
        $subCategory = 222;
        $description = 'file description';

        $cmdData = [
            'csvContent' => $csvContent,
            'fileDescription' => $description,
            'category' => $category,
            'subCategory' => $subCategory,
        ];

        $cmd = UploadCsvCmd::create($cmdData);

        $uploadData = [
            'content' => $encodedContent,
            'category' => $category,
            'subCategory' => $subCategory,
            'filename' => $description . '.csv',
            'description' => $description,
            'user' => IdentityProviderInterface::SYSTEM_USER,
        ];

        $uploadResultMsg = 'upload result';
        $uploadResult = new Result();
        $uploadResult->addMessage($uploadResultMsg);

        $this->expectedSideEffect(Upload::class, $uploadData, $uploadResult);

        $expectedResult = [
            'id' => [],
            'messages' => [
                0 => UploadCsvHandler::EMPTY_MSG,
                1 => $uploadResultMsg,
            ],
        ];

        $result = $this->sut->handleCommand($cmd);
        $this->assertEquals($expectedResult, $result->toArray());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpHandleCommand')]
    public function testHandleCommand(mixed $cmdUserId, mixed $attachedUserId): void
    {
        $csvContent = [
            [
                'heading 1' => 'row 1',
            ],
            [
                'heading 1' => 'row 2',
            ],
        ];

        $encodedContent = 'ImhlYWRpbmcgMSIKInJvdyAxIgoicm93IDIiCg==';

        $category = 111;
        $subCategory = 222;
        $description = 'file description';

        $cmdData = [
            'csvContent' => $csvContent,
            'fileDescription' => $description,
            'category' => $category,
            'subCategory' => $subCategory,
            'user' => $cmdUserId
        ];

        $cmd = UploadCsvCmd::create($cmdData);

        $uploadData = [
            'content' => $encodedContent,
            'category' => $category,
            'subCategory' => $subCategory,
            'filename' => $description . '.csv',
            'description' => $description,
            'user' => $attachedUserId,
        ];

        $uploadResultMsg = 'upload result';
        $uploadResult = new Result();
        $uploadResult->addMessage($uploadResultMsg);

        $this->expectedSideEffect(Upload::class, $uploadData, $uploadResult);

        $expectedResult = [
            'id' => [],
            'messages' => [
                0 => $uploadResultMsg,
                1 => sprintf(UploadCsvHandler::CONFIRM_MSG, count($csvContent)),
            ],
        ];

        $result = $this->sut->handleCommand($cmd);
        $this->assertEquals($expectedResult, $result->toArray());
    }

    public static function dpHandleCommand(): array
    {
        return [
            [null, IdentityProviderInterface::SYSTEM_USER],
            [291, 291],
        ];
    }
}
