<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Sms;

use Alphagov\Notifications\Client;
use Dvsa\Olcs\Api\Service\Sms\NotifySmsTransport;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(NotifySmsTransport::class)]
final class NotifySmsTransportTest extends TestCase
{
    public function testSendsBodyAsPersonalisationAndReturnsId(): void
    {
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('sendSms')
            ->with('+447700900123', 'tmpl', ['body' => 'Hello'], '42')
            ->willReturn(['id' => 'abc']);

        $this->assertSame('abc', (new NotifySmsTransport($client))->send('+447700900123', 'tmpl', 'Hello', '42'));
    }
}
