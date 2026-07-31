<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Email\Transport;

use Dvsa\Olcs\Email\Transport\DevNotifyTransport;
use Dvsa\Olcs\Email\Transport\GovUkNotifyTransport;
use League\CommonMark\ConverterInterface;
use League\CommonMark\Output\RenderedContent;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email as SymfonyEmail;

final class DevNotifyTransportTest extends MockeryTestCase
{
    /** @var TransportInterface&\Mockery\MockInterface */
    private $inner;
    /** @var ConverterInterface&\Mockery\MockInterface */
    private $converter;
    private DevNotifyTransport $sut;

    #[\Override]
    public function setUp(): void
    {
        $this->inner = m::mock(TransportInterface::class);
        $this->converter = m::mock(ConverterInterface::class);
        $this->sut = new DevNotifyTransport(
            $this->inner,
            $this->converter,
            '<html>{{subject}}<div>{{body}}</div></html>',
        );
    }

    public function testRendersMarkdownAndStripsHeader(): void
    {
        $email = new SymfonyEmail()
            ->from(new Address('from@example.com'))
            ->to(new Address('user@example.com'))
            ->subject('Hello')
            ->text('plain');
        $email->getHeaders()->addTextHeader(
            GovUkNotifyTransport::PAYLOAD_HEADER,
            json_encode(['markdownBody' => 'A short message'], JSON_THROW_ON_ERROR),
        );

        // The body is rendered via NotifyChrome (Notify-style Markdown emulation + inline styles).
        $rendered = new RenderedContent(new \League\CommonMark\Node\Block\Document(), '<p>A short message</p>');
        $this->converter->shouldReceive('convert')->with('A short message')->andReturn($rendered);

        $this->inner->shouldReceive('send')->once()->withArgs(function (SymfonyEmail $msg) {
            $this->assertStringContainsString('<p style=', (string) $msg->getHtmlBody()); // Notify inline styles applied
            $this->assertStringContainsString('A short message', (string) $msg->getHtmlBody());
            $this->assertStringContainsString('Hello', (string) $msg->getHtmlBody());
            $this->assertFalse($msg->getHeaders()->has(GovUkNotifyTransport::PAYLOAD_HEADER));
            return true;
        })->andReturn(null);

        $this->sut->send($email);
    }

    public function testPassesThroughWhenHeaderAbsent(): void
    {
        $email = new SymfonyEmail()
            ->from(new Address('from@example.com'))
            ->to(new Address('user@example.com'))
            ->subject('Legacy')
            ->text('plain')
            ->html('<p>html</p>');

        $this->converter->shouldNotReceive('convert');
        $this->inner->shouldReceive('send')->once()->andReturn(null);

        $this->sut->send($email);
    }
}
