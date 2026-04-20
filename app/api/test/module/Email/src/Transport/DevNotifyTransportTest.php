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

class DevNotifyTransportTest extends MockeryTestCase
{
    /** @var TransportInterface&\Mockery\MockInterface */
    private $inner;
    /** @var ConverterInterface&\Mockery\MockInterface */
    private $converter;
    private DevNotifyTransport $sut;

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
        $email = (new SymfonyEmail())
            ->from(new Address('from@example.com'))
            ->to(new Address('user@example.com'))
            ->subject('Hello')
            ->text('plain');
        $email->getHeaders()->addTextHeader(
            GovUkNotifyTransport::PAYLOAD_HEADER,
            json_encode(['markdownBody' => '**bold**'], JSON_THROW_ON_ERROR),
        );

        $rendered = new RenderedContent(m::mock('League\CommonMark\Node\Node'), '<p><strong>bold</strong></p>');
        $this->converter->shouldReceive('convert')->with('**bold**')->andReturn($rendered);

        $this->inner->shouldReceive('send')->once()->withArgs(function (SymfonyEmail $msg) {
            $this->assertStringContainsString('<strong>bold</strong>', $msg->getHtmlBody());
            $this->assertStringContainsString('Hello', $msg->getHtmlBody());
            $this->assertFalse($msg->getHeaders()->has(GovUkNotifyTransport::PAYLOAD_HEADER));
            return true;
        })->andReturn(null);

        $this->sut->send($email);
    }

    public function testPassesThroughWhenHeaderAbsent(): void
    {
        $email = (new SymfonyEmail())
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
