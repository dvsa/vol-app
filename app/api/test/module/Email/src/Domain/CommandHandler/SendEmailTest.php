<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Email\Domain\CommandHandler;

use Dvsa\Olcs\Email\Service\Email;
use Mockery as m;
use Dvsa\Olcs\Email\Domain\CommandHandler\SendEmail;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Laminas\I18n\Translator\Translator;
use Dvsa\Olcs\Email\Domain\Command\SendEmail as Cmd;
use Dvsa\Olcs\Api\Service\File\ContentStoreFileUploader;
use Dvsa\Olcs\DocumentShare\Data\Object\File;
use Dvsa\Olcs\Api\Entity\Doc\Document as DocumentEntity;
use Dvsa\Olcs\Api\Domain\Repository\Document as DocumentRepo;

/**
 * Send Email Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class SendEmailTest extends AbstractCommandHandlerTestCase
{
    /**
     * @var SendEmail
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new SendEmail();
        $this->mockRepo('Document', DocumentRepo::class);

        $this->mockedSmServices['config'] = [
            'email' => [
                'from_name' => 'Terry',
                'from_email' => 'terry.valtech@gmail.com',
                'send_all_mail_to' => 'terry.valtech@gmail.com',
                'selfserve_uri' => 'http://olcs-selfserve/',
                'internal_uri' => 'http://olcs-internal/'
            ]
        ];

        $this->mockedSmServices['FileUploader'] = m::mock(ContentStoreFileUploader::class);

        $this->mockedSmServices['translator'] = m::mock(Translator::class);
        $this->mockedSmServices['translator']->shouldReceive('translate')->andReturnUsing(
            fn($message) => 'translated-' . $message
        );

        $this->mockedSmServices['EmailService'] = m::mock(Email::class);

        parent::setUp();
    }

    public function testHandleCommandEmptyBody(): void
    {
        $this->expectException(\RuntimeException::class);

        $data = [
            'plainBody' => ''
        ];

        $command = Cmd::create($data);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommand(): void
    {
        $data = [
            'to' => 'foo@bar.com',
            'cc' => ['bar@foo.com'],
            'bcc' => ['bcc@foobar.com'],
            'docs' => [],
            'subject' => 'Some subject',
            'plainBody' => 'This is the email',
            'htmlBody' => null,
            'highPriority' => false,
            'subjectVariables' => []
        ];

        $command = Cmd::create($data);

        $this->repoMap['Document']
            ->shouldReceive('fetchByIds')
            ->once()
            ->with([])
            ->andReturn([]);

        $this->mockedSmServices['EmailService']->shouldReceive('send')
            ->once()
            ->with(
                'terry.valtech@gmail.com',
                'Terry',
                'terry.valtech@gmail.com',
                'foo@bar.com : translated-Some subject',
                'This is the email',
                null,
                [],
                [],
                [],
                false
            );

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandAlt(): void
    {
        $docId1 = 33;
        $docIdentifier1 = 'abcde123';
        $docFileName1 = 'filename1';
        $docPath1 = '/path/to/' . $docFileName1;
        $docContent1 = 'content1';

        $docId2 = 66;
        $docIdentifier2 = 'fghij456';
        $docFileName2 = 'filename2';
        $docPath2 = '/path/to/' . $docFileName2;
        $docContent2 = 'content2';

        $docIds = [$docId1, $docId2];

        $data = [
            'fromName' => 'Foo',
            'fromEmail' => 'foobar@cake.com',
            'to' => 'foo@bar.com',
            'cc' => ['bar@foo.com'],
            'bcc' => ['bcc@foobar.com'],
            'docs' => $docIds,
            'subject' => 'Some subject',
            'plainBody' => 'This is the email http://selfserve/ http://internal/',
            'htmlBody' => 'This is the html email http://selfserve/ http://internal/',
            'highPriority' => true,
            'subjectVariables' => []
        ];

        $command = Cmd::create($data);

        $this->sut->setSendAllMailTo(null);

        $document1 = m::mock(DocumentEntity::class);
        $document1->shouldReceive('getIdentifier')->once()->andReturn($docIdentifier1);
        $document1->shouldReceive('getFilename')->once()->andReturn($docPath1);
        $document2 = m::mock(DocumentEntity::class);
        $document2->shouldReceive('getIdentifier')->once()->andReturn($docIdentifier2);
        $document2->shouldReceive('getFilename')->once()->andReturn($docPath2);

        $docFile1 = m::mock(File::class);
        $docFile1->shouldReceive('getContent')->once()->andReturn($docContent1);

        $docFile2 = m::mock(File::class);
        $docFile2->shouldReceive('getContent')->once()->andReturn($docContent2);

        $this->repoMap['Document']
            ->shouldReceive('fetchByIds')
            ->once()
            ->with($docIds)
            ->andReturn([$document1, $document2]);

        $this->mockedSmServices['FileUploader']
            ->shouldReceive('download')
            ->once()
            ->with($docIdentifier1)
            ->andReturn($docFile1);

        $this->mockedSmServices['FileUploader']
            ->shouldReceive('download')
            ->once()
            ->with($docIdentifier2)
            ->andReturn($docFile2);

        $expectedDocs = [
            0 => [
                'fileName' => $docFileName1,
                'content' => $docContent1
            ],
            1 => [
                'fileName' => $docFileName2,
                'content' => $docContent2
            ]
        ];

        $this->mockedSmServices['EmailService']->shouldReceive('send')
            ->once()
            ->with(
                'foobar@cake.com',
                'Foo',
                'foo@bar.com',
                'translated-Some subject',
                'This is the email http://olcs-selfserve/ http://olcs-internal/',
                'This is the html email http://olcs-selfserve/ http://olcs-internal/',
                ['bar@foo.com'],
                ['bcc@foobar.com'],
                $expectedDocs,
                true
            );

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandNoAttachment(): void
    {
        $this->expectException(\RuntimeException::class);

        $docId1 = 33;
        $docIdentifier1 = 'abcde123';

        $docIds = [$docId1];

        $data = [
            'fromName' => 'Foo',
            'fromEmail' => 'foobar@cake.com',
            'to' => 'foo@bar.com',
            'cc' => ['bar@foo.com'],
            'bcc' => ['bcc@foobar.com'],
            'docs' => $docIds,
            'subject' => 'Some subject',
            'plainBody' => 'This is the email http://selfserve/ http://internal/',
            'htmlBody' => 'This is the html email http://selfserve/ http://internal/',
            'highPriority' => false,
            'subjectVariables' => []
        ];

        $command = Cmd::create($data);

        $this->sut->setSendAllMailTo(null);

        $document1 = m::mock(DocumentEntity::class);
        $document1->shouldReceive('getIdentifier')->once()->andReturn($docIdentifier1);

        $this->repoMap['Document']
            ->shouldReceive('fetchByIds')
            ->once()
            ->with($docIds)
            ->andReturn([$document1]);

        $this->mockedSmServices['FileUploader']
            ->shouldReceive('download')
            ->once()
            ->with($docIdentifier1)
            ->andReturn(null);

        $this->sut->handleCommand($command);
    }
}
