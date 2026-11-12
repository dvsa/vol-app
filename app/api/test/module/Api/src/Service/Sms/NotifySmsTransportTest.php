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

    public function testFindByReferenceReturnsTheMatchingSmsId(): void
    {
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('listNotifications')
            ->with(['reference' => 'dev-sms-42', 'template_type' => 'sms'])
            ->willReturn(['notifications' => [
                ['id' => 'other', 'reference' => 'int-sms-42', 'type' => 'sms'],
                ['id' => 'abc', 'reference' => 'dev-sms-42', 'type' => 'sms'],
            ]]);

        $this->assertSame('abc', (new NotifySmsTransport($client))->findByReference('dev-sms-42'));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('noMatch')]
    public function testFindByReferenceReturnsNullWithNoMatch(mixed $response): void
    {
        $client = $this->createStub(Client::class);
        $client->method('listNotifications')->willReturn($response);

        $this->assertNull((new NotifySmsTransport($client))->findByReference('dev-sms-42'));
    }

    public static function noMatch(): array
    {
        return [
            'not found' => [null],
            'empty' => [['notifications' => []]],
            'other reference' => [['notifications' => [['id' => 'abc', 'reference' => '420']]]],
            'other environment' => [['notifications' => [['id' => 'abc', 'reference' => 'int-sms-42']]]],
        ];
    }
}
