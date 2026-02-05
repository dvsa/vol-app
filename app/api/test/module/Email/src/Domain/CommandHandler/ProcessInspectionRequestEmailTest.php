<?php

declare(strict_types=1);

/**
 * Process Inspection Request Email Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Email\Domain\CommandHandler;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Email\Domain\Command\UpdateInspectionRequest;
use Dvsa\Olcs\Email\Service\Imap;
use Mockery as m;
use Dvsa\Olcs\Email\Domain\CommandHandler\ProcessInspectionRequestEmail;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Email\Domain\Command\ProcessInspectionRequestEmail as Cmd;
use Olcs\Logging\Log\Logger;

/**
 * Process Inspection Request Email Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class ProcessInspectionRequestEmailTest extends AbstractCommandHandlerTestCase
{
    /**
     * @var ProcessInspectionRequestEmail
     */
    protected $sut;

    protected $logWriter;

    public function setUp(): void
    {
        $this->sut = new ProcessInspectionRequestEmail();

        $this->mockedSmServices['ImapService'] = m::mock(Imap::class);

        parent::setUp();

        $this->logWriter = $logWriter = new \Laminas\Log\Writer\Mock();
        Logger::getLogger()->addWriter($logWriter);
    }

    public function testHandleCommand(): void
    {
        // stub data
        $emails = [
            1 => '4355',
            2 => '4356',
        ];
        $email1 = [
            'subject' => '[ Maintenance Inspection ] REQUEST=23456,STATUS=S',
        ];
        $email2 = [
            'subject' => '[ Maintenance Inspection ] REQUEST=23457,STATUS=U',
        ];

        $this->mockedSmServices['ImapService']->shouldReceive('connect')->once()->with('inspection_request');

        $this->mockedSmServices['ImapService']->shouldReceive('getMessages')
            ->once()
            ->andReturn($emails);

        $this->mockedSmServices['ImapService']->shouldReceive('getMessage')
            ->once()
            ->with(4355)
            ->andReturn($email1);

        $this->mockedSmServices['ImapService']->shouldReceive('getMessage')
            ->once()
            ->with(4356)
            ->andReturn($email2);

        $result = new Result();
        $data = ['id' => '23456', 'status' => 'S'];
        $this->expectedSideEffect(UpdateInspectionRequest::class, $data, $result);

        $data = ['id' => '23457', 'status' => 'U'];
        $this->expectedSideEffectThrowsException(UpdateInspectionRequest::class, $data, new \RuntimeException());

        // should only delete on success
        $this->mockedSmServices['ImapService']->shouldReceive('removeMessage')->once()->with(4355);

        $this->sut->handleCommand(Cmd::create([]));
    }

    public function testHandleCommandEmailFail(): void
    {
        $emails = [1 => '4355'];
        $email1 = ['error'];

        $this->mockedSmServices['ImapService']->shouldReceive('connect')->once()->with('inspection_request');

        $this->mockedSmServices['ImapService']->shouldReceive('getMessages')
            ->once()
            ->andReturn($emails);

        $this->mockedSmServices['ImapService']->shouldReceive('getMessage')
            ->once()
            ->with(4355)
            ->andReturn($email1);

        $this->sut->handleCommand(Cmd::create([]));

        $this->assertMatchesRegularExpression('/Could not retrieve email 4355/', $this->logWriter->events[0]['message']);
    }

    public function testHandleCommandEmailInvalidSubject(): void
    {
        $emails = [1 => '4355'];
        $email1 = ['subject' => 'spam!'];

        $this->mockedSmServices['ImapService']->shouldReceive('connect')->once()->with('inspection_request');

        $this->mockedSmServices['ImapService']->shouldReceive('getMessages')
            ->once()
            ->andReturn($emails);

        $this->mockedSmServices['ImapService']->shouldReceive('getMessage')
            ->once()
            ->with(4355)
            ->andReturn($email1);

        $this->sut->handleCommand(Cmd::create([]));

        $this->assertMatchesRegularExpression('/Unable to parse email subject line: spam!/', $this->logWriter->events[0]['message']);
    }

    public function testHandleCommandNoEmails(): void
    {
        // stub data
        $emails = [];

        $this->mockedSmServices['ImapService']->shouldReceive('connect')->once()->with('inspection_request');

        $this->mockedSmServices['ImapService']->shouldReceive('getMessages')
            ->once()
            ->andReturn($emails);

        $this->sut->handleCommand(Cmd::create([]));
    }

    public function testHandleCommandNotFound(): void
    {
        $emails = [1 => '4355'];
        $email1 = ['subject' => '[ Maintenance Inspection ] REQUEST=23456,STATUS=S',];

        $this->mockedSmServices['ImapService']->shouldReceive('connect')->once()->with('inspection_request');

        $this->mockedSmServices['ImapService']->shouldReceive('getMessages')
            ->once()
            ->andReturn($emails);

        $this->mockedSmServices['ImapService']->shouldReceive('getMessage')
            ->once()
            ->with(4355)
            ->andReturn($email1);

        $data = ['id' => '23456', 'status' => 'S'];
        $this->expectedSideEffectThrowsException(UpdateInspectionRequest::class, $data, new NotFoundException());

        $this->sut->handleCommand(Cmd::create([]));

        // assertions
        $expectedLogMessage = '==Unable to find the inspection request from the email subject line: '
            . '[ Maintenance Inspection ] REQUEST=23456,STATUS=S';

        $this->assertEquals($expectedLogMessage, $this->logWriter->events[0]['message']);
    }
}
