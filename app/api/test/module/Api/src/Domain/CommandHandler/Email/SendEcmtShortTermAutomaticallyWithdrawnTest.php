<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Email;

use Dvsa\Olcs\Api\Domain\Command\Email\SendEcmtShortTermAutomaticallyWithdrawn
    as SendEcmtShortTermAutomaticallyWithdrawnCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Email\SendEcmtShortTermAutomaticallyWithdrawn
    as SendEcmtShortTermAutomaticallyWithdrawnHandler;
use Dvsa\Olcs\Email\Data\Message;
use Dvsa\Olcs\Email\Domain\Command\SendEmail;
use Dvsa\Olcs\Email\Service\TemplateRenderer;
use Mockery as m;

/**
 * Test the short term permit automatically withdrawn email
 */
class SendEcmtShortTermAutomaticallyWithdrawnTest extends AbstractPermitTestCase
{
    public $orgEmails;
    public $contactDetails;
    public $userEmail;
    public $orgEmail1;
    public $orgEmail2;
    protected $commandClass = SendEcmtShortTermAutomaticallyWithdrawnCmd::class;
    protected $commandHandlerClass = SendEcmtShortTermAutomaticallyWithdrawnHandler::class;
    protected $template = 'ecmt-short-term-automatically-withdrawn';
    protected $subject = 'email.ecmt.short.term.automatically.withdrawn.subject';
    protected $permitApplicationRepo = 'IrhpApplication';
    protected $applicationEntityClass = IrhpApplication::class;

    /**
     * test handle command
     */
    public function testHandleCommand(): void
    {
        $paymentDeadlineNumDays = 10;

        $templateVars = [
            'applicationRef' => $this->applicationRef,
            'paymentDeadlineNumDays' => $paymentDeadlineNumDays,
        ];

        $this->mockedSmServices['PermitsFeesDaysToPayIssueFeeProvider']->shouldReceive('getDays')
            ->once()
            ->withNoArgs()
            ->andReturn($paymentDeadlineNumDays);

        $this->mockedSmServices[TemplateRenderer::class]->shouldReceive('renderBody')->once()->with(
            m::type(Message::class),
            $this->template,
            $templateVars,
            'default'
        );

        $this->applicationEntity->shouldReceive('getCreatedBy')->once()->withNoArgs()->andReturn($this->userEntity);

        $this->organisation->shouldReceive('getAdminEmailAddresses')->once()->andReturn($this->orgEmails);

        $this->contactDetails->shouldReceive('getEmailAddress')->once()->withNoArgs()->andReturn($this->userEmail);
        $this->userEntity->shouldReceive('isInternal')->once()->withNoArgs()->andReturn(false);
        $this->userEntity->shouldReceive('getContactDetails')->once()->withNoArgs()->andReturn($this->contactDetails);

        $expectedData = [
            'to' => $this->userEmail,
            'locale' => 'en_GB',
            'subject' => $this->subject,
        ];

        $this->expectedSideEffect(SendEmail::class, $expectedData, new Result());

        $result = $this->sut->handleCommand($this->commandEntity);

        $this->assertSame(['IrhpApplication' => $this->permitAppId], $result->getIds());
        $this->assertSame(['Email sent'], $result->getMessages());

        /** @var Message $message */
        $message = $this->sut->getMessage();
        $this->assertSame($this->userEmail, $message->getTo());
        $this->assertSame($this->orgEmails, $message->getCc());
        $this->assertSame($this->subject, $message->getSubject());
    }

    public function testHandleCommandForCreatedByInternalUser(): void
    {
        $paymentDeadlineNumDays = 10;

        $templateVars = [
            'applicationRef' => $this->applicationRef,
            'paymentDeadlineNumDays' => $paymentDeadlineNumDays,
        ];

        $this->mockedSmServices['PermitsFeesDaysToPayIssueFeeProvider']->shouldReceive('getDays')
            ->once()
            ->withNoArgs()
            ->andReturn($paymentDeadlineNumDays);

        $this->mockedSmServices[TemplateRenderer::class]->shouldReceive('renderBody')->once()->with(
            m::type(Message::class),
            $this->template,
            $templateVars,
            'default'
        );

        $this->applicationEntity->shouldReceive('getCreatedBy')->once()->withNoArgs()->andReturn($this->userEntity);

        $this->organisation->shouldReceive('getAdminEmailAddresses')->once()->andReturn($this->orgEmails);

        $this->userEntity->shouldReceive('isInternal')->once()->withNoArgs()->andReturn(true);
        $this->userEntity->shouldReceive('getContactDetails')->never();

        $expectedData = [
            'to' => $this->orgEmail1,
            'locale' => 'en_GB',
            'subject' => $this->subject,
        ];

        $this->expectedSideEffect(SendEmail::class, $expectedData, new Result());

        $result = $this->sut->handleCommand($this->commandEntity);

        $this->assertSame(['IrhpApplication' => $this->permitAppId], $result->getIds());
        $this->assertSame(['Email sent'], $result->getMessages());

        /** @var Message $message */
        $message = $this->sut->getMessage();
        $this->assertSame($this->orgEmail1, $message->getTo());
        $this->assertSame([1 => $this->orgEmail2], $message->getCc());
        $this->assertSame($this->subject, $message->getSubject());
    }
}
