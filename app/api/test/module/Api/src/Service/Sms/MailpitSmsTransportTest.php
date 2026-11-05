<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Sms;

use Dvsa\Olcs\Api\Service\Sms\MailpitSmsTransport;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[\PHPUnit\Framework\Attributes\CoversClass(MailpitSmsTransport::class)]
final class MailpitSmsTransportTest extends TestCase
{
    public function testSendsPlainEmail(): void
    {
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects($this->once())->method('send')->with($this->callback(function (Email $e): bool {
            return $e->getSubject() === 'SMS to +447700900123'
                && $e->getTextBody() === 'Hello'
                && $e->getHtmlBody() === null
                && $e->getTo()[0]->getAddress() === 'sms@localhost'
                && $e->getFrom()[0]->getAddress() === 'from@example.com';
        }));

        $sut = new MailpitSmsTransport($mailer, 'from@example.com', 'From Name');

        $this->assertNull($sut->send('+447700900123', 'tmpl', 'Hello', '1'));
    }

    public function testFindByReferenceIsAlwaysNull(): void
    {
        $sut = new MailpitSmsTransport($this->createStub(MailerInterface::class));

        $this->assertNull($sut->findByReference('1'));
    }
}
