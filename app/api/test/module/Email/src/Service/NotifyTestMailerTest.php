<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Email\Service;

use Dvsa\Olcs\Email\Service\NotifyTestMailer;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email as SymfonyEmail;

class NotifyTestMailerTest extends MockeryTestCase
{
    public function testIsDisabledWhenMailerIsNull(): void
    {
        $sut = new NotifyTestMailer(null);

        $this->assertFalse($sut->isEnabled());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('NotifyTestMailer is not configured');
        $sut->send(new SymfonyEmail());
    }

    public function testIsEnabledAndDelegatesSendWhenMailerInjected(): void
    {
        $inner = m::mock(MailerInterface::class);
        $email = new SymfonyEmail();

        $inner->shouldReceive('send')->once()->with($email);

        $sut = new NotifyTestMailer($inner);

        $this->assertTrue($sut->isEnabled());
        $sut->send($email);
    }
}
