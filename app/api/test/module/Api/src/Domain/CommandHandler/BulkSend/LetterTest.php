<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\BulkSend;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\BulkSend\Letter;
use Dvsa\Olcs\Api\Domain\Repository\DocTemplate as DocTemplateRepo;
use Dvsa\Olcs\Api\Entity\Doc\DocTemplate;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Api\Service\File\ContentStoreFileUploader;
use Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStore as GenerateAndStoreCmd;
use Dvsa\Olcs\DocumentShare\Data\Object\File as ContentStoreFile;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;
use LmcRbacMvc\Service\AuthorizationService;

class LetterTest extends AbstractCommandHandlerTestCase
{
    private $mockFileUploader;

    public function setUp(): void
    {
        $this->sut = new Letter();

        $this->mockRepo('DocTemplate', DocTemplateRepo::class);

        $this->mockFileUploader = m::mock(ContentStoreFileUploader::class);
        $this->mockedSmServices['FileUploader'] = $this->mockFileUploader;

        $mockUser = m::mock(User::class)->makePartial();
        $mockUser->setId(291);

        $mockAuthSrv = m::mock(AuthorizationService::class);
        $mockAuthSrv->shouldReceive('getIdentity->getUser')->andReturn($mockUser);
        $this->mockedSmServices[AuthorizationService::class] = $mockAuthSrv;

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $documentIdentifier = 'documentIdentifier';

        $fileContents = "1\n" .
            "4\n" .
            "4\n" .
            "8\n";

        $file = m::mock(ContentStoreFile::class);
        $file->shouldReceive('getContent')
            ->once()
            ->withNoArgs()
            ->andReturn($fileContents);

        $this->mockFileUploader->shouldReceive('download')
            ->with($documentIdentifier)
            ->once()
            ->andReturn($file);

        $template = m::mock(DocTemplate::class);

        $this->repoMap['DocTemplate']
            ->shouldReceive('fetchByTemplateSlug')
            ->with('template-slug')
            ->once()
            ->andReturn($template);

        $template->shouldReceive('getDocument->getIdentifier')
            ->times(4)
            ->andReturn('SOME-DOC');

        $template->shouldReceive('getDescription')->andReturn('SOME-DESC');

        $processResult = new Result();
        $processResult->addMessage('Generating Doc');

        $command = m::mock(CommandInterface::class);

        $command->shouldReceive('getDocumentIdentifier')
            ->once()
            ->withNoArgs()
            ->andReturn($documentIdentifier);

        $command->shouldReceive('getTemplateSlug')
            ->once()
            ->withNoArgs()
            ->andReturn('template-slug');

        $command->shouldReceive('getUser')
            ->times(4)
            ->withNoArgs()
            ->andReturn(291);

        $this->expectedSideEffect(
            GenerateAndStoreCmd::class,
            [
                'template' => $template->getDocument()->getIdentifier(),
                'query' => ['licence' => 1, 'user' => $command->getUser()],
                'description' => $template->getDescription(),
                'category' => Category::CATEGORY_REPORT,
                'subCategory' => SubCategory::DOC_SUB_CATEGORY_REPORT_LETTER,
                'isExternal' => false,
                'isScan' => false,
                'disableBookmarks' => false,
                'licence' => 1,
                'dispatch' => true
            ],
            $processResult
        );

        $this->expectedSideEffect(
            GenerateAndStoreCmd::class,
            [
                'template' => $template->getDocument()->getIdentifier(),
                'query' => ['licence' => 4, 'user' => $command->getUser()],
                'description' => $template->getDescription(),
                'category' => Category::CATEGORY_REPORT,
                'subCategory' => SubCategory::DOC_SUB_CATEGORY_REPORT_LETTER,
                'isExternal' => false,
                'isScan' => false,
                'disableBookmarks' => false,
                'licence' => 4,
                'dispatch' => true
            ],
            $processResult
        );

        $this->expectedSideEffect(
            GenerateAndStoreCmd::class,
            [
                'template' => $template->getDocument()->getIdentifier(),
                'query' => ['licence' => 8, 'user' => $command->getUser()],
                'description' => $template->getDescription(),
                'category' => Category::CATEGORY_REPORT,
                'subCategory' => SubCategory::DOC_SUB_CATEGORY_REPORT_LETTER,
                'isExternal' => false,
                'isScan' => false,
                'disableBookmarks' => false,
                'licence' => 8,
                'dispatch' => true
            ],
            $processResult
        );

        $result = $this->sut->handleCommand($command);

        $expectedMessages = [
            'Generating Doc',
            'Generating Doc',
            'Generating Doc',
            'Processing completed successfully',
        ];

        $this->assertEquals($expectedMessages, $result->getMessages());
    }
}
