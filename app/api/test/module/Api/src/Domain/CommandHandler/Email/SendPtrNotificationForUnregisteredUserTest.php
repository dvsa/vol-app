<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Email;

use Dvsa\Olcs\Api\Domain\Command\Email\SendPtrNotificationForUnregisteredUser as Cmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Email\SendPtrNotificationForUnregisteredUser;
use Dvsa\Olcs\Email\Data\Message;
use Dvsa\Olcs\Email\Domain\Command\SendEmail;
use Dvsa\Olcs\Email\Service\TemplateRenderer;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

class SendPtrNotificationForUnregisteredUserTest extends AbstractCommandHandlerTestCase
{
    /** @var CommandInterface|SendPtrNotificationForUnregisteredUser */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new SendPtrNotificationForUnregisteredUser();

        $this->mockedSmServices = [
            TemplateRenderer::class => m::mock(TemplateRenderer::class),
        ];

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $emailAddress = 'testMail@example.com';
        $command = Cmd::create([
            'emailAddress' => $emailAddress,
            'translateToWelsh' => 'N'
        ]);

        $this->mockedSmServices[TemplateRenderer::class]->shouldReceive('renderBody')->with(
            m::type(Message::class),
            'ptr-notification_unregistered-user',
            [],
            'default'
        );

        $this->expectedSideEffect(
            SendEmail::class,
            [
                'to' => 'testMail@example.com',
                'subject' => 'email.insolvent-company-notification.subject'
            ],
            new Result()
        );

        /** @var Result $result */
        $result = $this->sut->handleCommand($command);

        $this->assertSame(['Email sent'], $result->getMessages());
    }
}
