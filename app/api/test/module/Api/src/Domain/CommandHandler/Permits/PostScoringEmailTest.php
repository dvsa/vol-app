<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Permits;

use Dvsa\Olcs\Api\Domain\Command\Email\SendEcmtApsgPostScoring;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Permits\PostScoringEmail;
use Dvsa\Olcs\Api\Domain\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Domain\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\Olcs\Api\Service\File\ContentStoreFileUploader;
use Dvsa\Olcs\DocumentShare\Data\Object\File as ContentStoreFile;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

class PostScoringEmailTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->mockRepo('IrhpApplication', IrhpApplicationRepo::class);

        $this->sut = new PostScoringEmail();

        $this->mockedSmServices['FileUploader'] = m::mock(ContentStoreFileUploader::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $documentIdentifier = 'documentIdentifier';

        $fileContents = "28,29\n" .
            "91\n" .
            "22\n" .
            "38\n" .
            "14\n" .
            "62";

        $file = m::mock(ContentStoreFile::class);
        $file->shouldReceive('getContent')
            ->andReturn($fileContents);

        $this->mockedSmServices['FileUploader']->shouldReceive('download')
            ->with($documentIdentifier)
            ->andReturn($file);

        $irhpApplication22 = m::mock(IrhpApplication::class);
        $irhpApplication22->shouldReceive('hasStateRequiredForPostScoringEmail')
            ->withNoArgs()
            ->andReturnFalse();

        $irhpApplication38Licence = m::mock(Licence::class);
        $irhpApplication38Licence->shouldReceive('hasStatusRequiredForPostScoringEmail')
            ->withNoArgs()
            ->andReturnFalse();

        $irhpApplication38 = m::mock(IrhpApplication::class);
        $irhpApplication38->shouldReceive('hasStateRequiredForPostScoringEmail')
            ->withNoArgs()
            ->andReturnTrue();
        $irhpApplication38->shouldReceive('getLicence')
            ->withNoArgs()
            ->andReturn($irhpApplication38Licence);

        $irhpApplication14Licence = m::mock(Licence::class);
        $irhpApplication14Licence->shouldReceive('hasStatusRequiredForPostScoringEmail')
            ->withNoArgs()
            ->andReturnTrue();

        $irhpApplication14 = m::mock(IrhpApplication::class);
        $irhpApplication14->shouldReceive('hasStateRequiredForPostScoringEmail')
            ->withNoArgs()
            ->andReturnTrue();
        $irhpApplication14->shouldReceive('getLicence')
            ->withNoArgs()
            ->andReturn($irhpApplication14Licence);

        $irhpApplication62Licence = m::mock(Licence::class);
        $irhpApplication62Licence->shouldReceive('hasStatusRequiredForPostScoringEmail')
            ->withNoArgs()
            ->andReturnTrue();

        $irhpApplication62 = m::mock(IrhpApplication::class);
        $irhpApplication62->shouldReceive('hasStateRequiredForPostScoringEmail')
            ->withNoArgs()
            ->andReturnTrue();
        $irhpApplication62->shouldReceive('getLicence')
            ->withNoArgs()
            ->andReturn($irhpApplication62Licence);

        $this->repoMap['IrhpApplication']->shouldReceive('fetchById')
            ->with(91)
            ->andThrow(new NotFoundException());
        $this->repoMap['IrhpApplication']->shouldReceive('fetchById')
            ->with(22)
            ->andReturn($irhpApplication22);
        $this->repoMap['IrhpApplication']->shouldReceive('fetchById')
            ->with(38)
            ->andReturn($irhpApplication38);
        $this->repoMap['IrhpApplication']->shouldReceive('fetchById')
            ->with(14)
            ->andReturn($irhpApplication14);
        $this->repoMap['IrhpApplication']->shouldReceive('fetchById')
            ->with(62)
            ->andReturn($irhpApplication62);

        $this->expectedEmailQueueSideEffect(
            SendEcmtApsgPostScoring::class,
            ['id' => '14'],
            '14',
            new Result()
        );

        $this->expectedEmailQueueSideEffect(
            SendEcmtApsgPostScoring::class,
            ['id' => '62'],
            '62',
            new Result()
        );

        $command = m::mock(CommandInterface::class);
        $command->shouldReceive('getDocumentIdentifier')
            ->andReturn($documentIdentifier);
        $result = $this->sut->handleCommand($command);

        $expectedMessages = [
            'Line 1: Ignored due to multiple columns',
            'Line 2: Ignored due to application id 91 not being found',
            'Line 3: Ignored due to application id 22 not being in the correct state for post scoring email',
            'Line 4: Ignored due to the licence associated with application id 38 not being ' .
                'in the correct state for post scoring email',
            'Line 5: Email sent for application id 14',
            'Line 6: Email sent for application id 62',
            'All lines processed'
        ];

        $this->assertEquals($expectedMessages, $result->getMessages());
    }
}
