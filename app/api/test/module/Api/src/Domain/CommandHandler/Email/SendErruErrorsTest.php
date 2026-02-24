<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Email;

use Dvsa\Olcs\Api\Domain\Command\Email\SendErruErrors as SendErruErrorsCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Email\SendErruErrors;
use Dvsa\Olcs\Api\Domain\Repository\ErruRequestFailure as ErruRequestFailureRepo;
use Dvsa\Olcs\Api\Entity\Doc\Document;
use Dvsa\Olcs\Api\Entity\Si\ErruRequestFailure;
use Dvsa\Olcs\Email\Data\Message;
use Dvsa\Olcs\Email\Domain\Command\SendEmail;
use Dvsa\Olcs\Email\Service\TemplateRenderer;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * SendErruErrorsTest
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class SendErruErrorsTest extends AbstractCommandHandlerTestCase
{
    /**
     * @var SendErruErrors
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new SendErruErrors();
        $this->mockRepo('ErruRequestFailure', ErruRequestFailureRepo::class);

        $this->mockedSmServices = [
            TemplateRenderer::class => m::mock(TemplateRenderer::class),
        ];

        parent::setUp();
    }

    /**
     * Tests handle command
     */
    public function testHandleCommand(): void
    {
        $requestFailureId = 1234;
        $notificationNumber = '0ffefb6b-6344-4a60-9a53-4381c32f98d9';
        $originatingAuthority = 'originating authority';
        $memberStateCode = 'PL';

        $filename = 'filename.rtf';
        $documentFilename = '/path/to/' . $filename;
        $documentId = 5678;

        $sentAt = '2014-02-20T16:22:09Z';

        $sentDateTime = new \DateTime($sentAt);
        $formattedSentAt = $sentDateTime->format(SendErruErrors::BODY_DATE_FORMAT);
        $subjectSentAt = $sentDateTime->format(SendErruErrors::SUBJECT_DATE_FORMAT);

        $inputData = [
            'notificationNumber' => $notificationNumber,
            'memberStateCode' => $memberStateCode,
            'originatingAuthority' => $originatingAuthority,
            'sentAt' => $sentAt,
        ];
        $inputJson = json_encode($inputData);

        $errors = ['error 1', 'error 2'];
        $errorJson = json_encode($errors);

        $command = SendErruErrorsCmd::create(['id' => $requestFailureId]);

        $documentEntity = m::mock(Document::class);
        $documentEntity->shouldReceive('getFilename')->once()->andReturn($documentFilename);
        $documentEntity->shouldReceive('getId')->once()->andReturn($documentId);

        $requestFailureEntity = m::mock(ErruRequestFailure::class);
        $requestFailureEntity->shouldReceive('getDocument')->once()->andReturn($documentEntity);
        $requestFailureEntity->shouldReceive('getErrors')->once()->andReturn($errorJson);
        $requestFailureEntity->shouldReceive('getInput')->once()->andReturn($inputJson);
        $requestFailureEntity->shouldReceive('getId')->once()->andReturn($requestFailureId);

        $this->repoMap['ErruRequestFailure']
            ->shouldReceive('fetchUsingId')
            ->with(m::type(CommandInterface::class))
            ->once()
            ->andReturn($requestFailureEntity);

        $this->mockedSmServices[TemplateRenderer::class]->shouldReceive('renderBody')->with(
            m::type(Message::class),
            SendErruErrors::EMAIL_TEMPLATE,
            [
                'sentAt' => $formattedSentAt,
                'notificationNumber' => $notificationNumber,
                'memberState' => $memberStateCode,
                'originatingAuthority' => $originatingAuthority,
                'errorMessages' => $errors,
                'filename' => $filename
            ],
            'default'
        );

        $result = new Result();
        $data = [
            'to' => SendErruErrors::EMAIL_ADDRESS,
            'locale' => 'en_GB',
            'subject' => SendErruErrors::EMAIL_SUBJECT
        ];

        $this->expectedSideEffect(SendEmail::class, $data, $result);

        $result = $this->sut->handleCommand($command);
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($this->sut->getMessage()->getSubjectVariables(), [$subjectSentAt, $notificationNumber]);
        $this->assertEquals($this->sut->getMessage()->getDocs(), [$documentId]);
    }

    /**
     * Tests handle command when there's no input data (likely xml failed to parse)
     */
    public function testHandleCommandWithNoInputData(): void
    {
        $requestFailureId = 1234;

        $filename = 'filename.rtf';
        $documentFilename = '/path/to/' . $filename;
        $documentId = 5678;

        $errors = ['error 1', 'error 2'];
        $errorJson = json_encode($errors);

        $command = SendErruErrorsCmd::create(['id' => $requestFailureId]);

        $documentEntity = m::mock(Document::class);
        $documentEntity->shouldReceive('getFilename')->once()->andReturn($documentFilename);
        $documentEntity->shouldReceive('getId')->once()->andReturn($documentId);

        $requestFailureEntity = m::mock(ErruRequestFailure::class);
        $requestFailureEntity->shouldReceive('getDocument')->once()->andReturn($documentEntity);
        $requestFailureEntity->shouldReceive('getErrors')->once()->andReturn($errorJson);
        $requestFailureEntity->shouldReceive('getInput')->once()->andReturn([]);
        $requestFailureEntity->shouldReceive('getId')->once()->andReturn($requestFailureId);

        $this->repoMap['ErruRequestFailure']
            ->shouldReceive('fetchUsingId')
            ->with(m::type(CommandInterface::class))
            ->once()
            ->andReturn($requestFailureEntity);

        $this->mockedSmServices[TemplateRenderer::class]->shouldReceive('renderBody')->with(
            m::type(Message::class),
            SendErruErrors::EMAIL_TEMPLATE,
            [
                'sentAt' => SendErruErrors::MISSING_INPUT,
                'notificationNumber' => SendErruErrors::MISSING_INPUT,
                'memberState' => SendErruErrors::MISSING_INPUT,
                'originatingAuthority' => SendErruErrors::MISSING_INPUT,
                'errorMessages' => $errors,
                'filename' => $filename
            ],
            'default'
        );

        $result = new Result();
        $data = [
            'to' => SendErruErrors::EMAIL_ADDRESS,
            'locale' => 'en_GB',
            'subject' => SendErruErrors::EMAIL_SUBJECT
        ];

        $this->expectedSideEffect(SendEmail::class, $data, $result);

        $result = $this->sut->handleCommand($command);
        $this->assertInstanceOf(Result::class, $result);
        $expectedSubjectVars = [SendErruErrors::UNKNOWN_DATE, SendErruErrors::UNKNOWN_BUSINESS_CASE];
        $this->assertEquals($this->sut->getMessage()->getSubjectVariables(), $expectedSubjectVars);
        $this->assertEquals($this->sut->getMessage()->getDocs(), [$documentId]);
    }
}
