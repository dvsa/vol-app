<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Cases\Si;

use Dvsa\Olcs\Api\Domain\Exception\InrClientException;
use Dvsa\Olcs\Api\Entity\Cases\Cases;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Si\SendResponse;
use Dvsa\Olcs\Api\Domain\Command\Cases\Si\SendResponse as SendResponseCmd;
use Dvsa\Olcs\Api\Domain\Repository\ErruRequest as ErruRequestRepo;
use Dvsa\Olcs\Api\Entity\Si\ErruRequest as ErruRequestEntity;
use Dvsa\Olcs\Api\Service\Nr\InrClient;
use Laminas\Http\Client\Adapter\Exception\RuntimeException as AdapterRuntimeException;
use Dvsa\Olcs\Api\Service\File\ContentStoreFileUploader;
use Dvsa\Olcs\DocumentShare\Data\Object\File;

class SendResponseTest extends AbstractCommandHandlerTestCase
{
    private readonly m\MockInterface $inrService;

    public function setUp(): void
    {
        $this->inrService = m::mock(InrClient::class)->shouldAllowMockingProtectedMethods();

        $this->sut = new SendResponse($this->inrService);
        $this->mockRepo('ErruRequest', ErruRequestRepo::class);

        $this->mockedSmServices = [
            'FileUploader' => m::mock(ContentStoreFileUploader::class)
        ];

        $this->refData = [
            ErruRequestEntity::FAILED_CASE_TYPE,
            ErruRequestEntity::SENT_CASE_TYPE
        ];

        parent::setUp();
    }

    /**
     * Tests sending the Msi response
     */
    public function testHandleCommand(): void
    {
        $xml = 'xml string';
        $xmlIdentifier = 'identifier';
        $erruId = 333;
        $command = SendResponseCmd::create(['id' => $erruId]);

        $xmlFile = m::mock(File::class);
        $xmlFile->shouldReceive('getContent')->once()->andReturn($xml);

        $this->mockedSmServices['FileUploader']
            ->shouldReceive('download')
            ->once()
            ->with($xmlIdentifier)
            ->andReturn($xmlFile);

        $case = m::mock(Cases::class);
        $case->expects('hasErruRequestedPenalties')->andReturnTrue();

        $erruRequest = m::mock(ErruRequestEntity::class)->makePartial();
        $erruRequest->expects('getCase')->withNoArgs()->andReturn($case);
        $erruRequest->shouldReceive('getId')->once()->andReturn($erruId);
        $erruRequest->shouldReceive('getResponseDocument->getIdentifier')->once()->andReturn($xmlIdentifier);
        $erruRequest
            ->shouldReceive('setMsiType')
            ->once()
            ->with($this->refData[ErruRequestEntity::SENT_CASE_TYPE]);

        $this->repoMap['ErruRequest']->shouldReceive('fetchUsingId')->once()->with($command)->andReturn($erruRequest);
        $this->repoMap['ErruRequest']->shouldReceive('save')->once()->with(m::type(ErruRequestEntity::class));

        $this->inrService
            ->expects('makeRequestReturnStatusCode')
            ->with($xml)
            ->andReturn(202);

        $this->inrService
            ->expects('close')
            ->withNoArgs();

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'Erru request' => $erruId
            ],
            'messages' => [
                'Msi Response sent'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
        $this->assertInstanceOf(Result::class, $result);
    }

    /**
     * Tests sending the Msi response when the response code is 400
     */
    public function testHandleCommandInvalidResponseCode(): void
    {
        $this->expectException(InrClientException::class);
        $this->expectExceptionMessage('INR Http response code was 400');

        $xml = 'xml string';
        $xmlIdentifier = 'identifier';
        $erruId = 333;
        $command = SendResponseCmd::create(['id' => $erruId]);

        $xmlFile = m::mock(File::class);
        $xmlFile->shouldReceive('getContent')->once()->andReturn($xml);

        $this->mockedSmServices['FileUploader']
            ->shouldReceive('download')
            ->once()
            ->with($xmlIdentifier)
            ->andReturn($xmlFile);

        $case = m::mock(Cases::class);
        $case->expects('hasErruRequestedPenalties')->andReturnTrue();

        $erruRequest = m::mock(ErruRequestEntity::class)->makePartial();
        $erruRequest->expects('getCase')->withNoArgs()->andReturn($case);
        $erruRequest->shouldReceive('getResponseDocument->getIdentifier')->once()->andReturn($xmlIdentifier);
        $erruRequest
            ->shouldReceive('setMsiType')
            ->once()
            ->with($this->refData[ErruRequestEntity::FAILED_CASE_TYPE]);

        $this->repoMap['ErruRequest']->shouldReceive('fetchUsingId')->once()->with($command)->andReturn($erruRequest);
        $this->repoMap['ErruRequest']->shouldReceive('save')->once()->with(m::type(ErruRequestEntity::class));

        $this->inrService
            ->expects('makeRequestReturnStatusCode')
            ->with($xml)
            ->andReturn(400);

        $this->inrService
            ->expects('close')
            ->withNoArgs();

        $this->sut->handleCommand($command);
    }

    /**
     * Tests sending the Msi response when the inr client throws an exception
     */
    public function testHandleCommandAdapterException(): void
    {
        $this->expectException(InrClientException::class);
        $this->expectExceptionMessage('There was an error sending the INR response adapter exception message');

        $xml = 'xml string';
        $xmlIdentifier = 'identifier';
        $erruId = 333;
        $command = SendResponseCmd::create(['id' => $erruId]);

        $xmlFile = m::mock(File::class);
        $xmlFile->shouldReceive('getContent')->once()->andReturn($xml);

        $this->mockedSmServices['FileUploader']
            ->shouldReceive('download')
            ->once()
            ->with($xmlIdentifier)
            ->andReturn($xmlFile);

        $case = m::mock(Cases::class);
        $case->expects('hasErruRequestedPenalties')->andReturnTrue();

        $erruRequest = m::mock(ErruRequestEntity::class)->makePartial();
        $erruRequest->expects('getCase')->withNoArgs()->andReturn($case);
        $erruRequest->shouldReceive('getResponseDocument->getIdentifier')->once()->andReturn($xmlIdentifier);
        $erruRequest
            ->shouldReceive('setMsiType')
            ->once()
            ->with($this->refData[ErruRequestEntity::FAILED_CASE_TYPE]);

        $this->repoMap['ErruRequest']->shouldReceive('fetchUsingId')->once()->with($command)->andReturn($erruRequest);
        $this->repoMap['ErruRequest']->shouldReceive('save')->once()->with(m::type(ErruRequestEntity::class));

        $this->inrService
            ->expects('makeRequestReturnStatusCode')
            ->with($xml)
            ->andThrow(AdapterRuntimeException::class, 'adapter exception message');

        $this->sut->handleCommand($command);
    }

    /**
     * Tests no attempt made to send when there are no requested penalties
     */
    public function testHandleCommandNoRequestedPenaltiesException(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(SendResponse::MSG_NO_RESPONSE_REQUIRED);

        $erruId = 333;
        $command = SendResponseCmd::create(['id' => $erruId]);

        $case = m::mock(Cases::class);
        $case->expects('hasErruRequestedPenalties')->andReturnFalse();

        $erruRequest = m::mock(ErruRequestEntity::class)->makePartial();
        $erruRequest->expects('getCase')->withNoArgs()->andReturn($case);

        $this->repoMap['ErruRequest']->shouldReceive('fetchUsingId')->once()->with($command)->andReturn($erruRequest);

        $this->sut->handleCommand($command);
    }
}
