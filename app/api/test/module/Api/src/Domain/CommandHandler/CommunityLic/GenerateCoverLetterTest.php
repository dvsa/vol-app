<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\CommunityLic;

use Dvsa\Olcs\Api\Domain\Command\CommunityLic\GenerateCoverLetter as GenerateCoverLetterCmd;
use Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStore;
use Dvsa\Olcs\Api\Domain\Command\PrintScheduler\Enqueue as EnqueueFileCommand;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\CommunityLic\GenerateCoverLetter;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Api\Entity\Doc\Document;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\CommandHandler\CommunityLic\GenerateCoverLetter::class)]
class GenerateCoverLetterTest extends AbstractCommandHandlerTestCase
{
    /** @var GenerateCoverLetter */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new GenerateCoverLetter();

        $this->mockRepo('Licence', LicenceRepo::class);

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpHandleCommandForGoods')]
    public function testHandleCommandForGoods(mixed $isNi, mixed $expectedTemplate): void
    {
        $licenceId = 100;
        $userId = 200;
        $documentId = 300;

        $data = [
            'licence' => $licenceId,
            'user' => $userId
        ];

        $command = GenerateCoverLetterCmd::create($data);

        $mockLicence = m::mock(Licence::class)
            ->shouldReceive('isPsv')
            ->withNoArgs()
            ->once()
            ->andReturn(false)
            ->shouldReceive('isNi')
            ->withNoArgs()
            ->once()
            ->andReturn($isNi)
            ->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($licenceId)
            ->getMock();

        $this->repoMap['Licence']
            ->shouldReceive('fetchById')
            ->with($licenceId)
            ->once()
            ->andReturn($mockLicence);

        $this->expectedSideEffect(
            GenerateAndStore::class,
            [
                'template' => $expectedTemplate,
                'query' => [
                    'licence' => $licenceId,
                ],
                'description' => 'UK licence for the Community cover letter',
                'category' => Category::CATEGORY_LICENSING,
                'subCategory' => SubCategory::DOC_SUB_CATEGORY_COMMUNITY_LICENCE_COVER,
                'isExternal' => false,
                'isScan' => false
            ],
            (new Result())->addId('document', $documentId)->addMessage('Document generated')
        );

        $this->expectedSideEffect(
            EnqueueFileCommand::class,
            [
                'documentId' => $documentId,
                'jobName' => 'UK licence for the Community cover letter',
                'user' => $userId,
            ],
            (new Result())->addMessage('Document scheduled for printing')
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'document' => $documentId
            ],
            'messages' => [
                'Document generated',
                'Document scheduled for printing',
                'UK licence for the Community cover letter processed',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public static function dpHandleCommandForGoods(): array
    {
        return [
            'GB' => [
                'isNi' => false,
                'expectedTemplate' => Document::GV_UK_COMMUNITY_LICENCE_GB_COVER_LETTER
            ],
            'NI' => [
                'isNi' => true,
                'expectedTemplate' => Document::GV_UK_COMMUNITY_LICENCE_NI_COVER_LETTER
            ],
        ];
    }

    public function testHandleCommandForPsv(): void
    {
        $licenceId = 100;
        $userId = 200;

        $data = [
            'licence' => $licenceId,
            'user' => $userId
        ];

        $command = GenerateCoverLetterCmd::create($data);

        $mockLicence = m::mock(Licence::class)
            ->shouldReceive('isPsv')
            ->withNoArgs()
            ->once()
            ->andReturn(true)
            ->getMock();

        $this->repoMap['Licence']
            ->shouldReceive('fetchById')
            ->with($licenceId)
            ->once()
            ->andReturn($mockLicence);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'UK licence for the Community cover letter not required for PSV',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
