<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Template;

use Dvsa\Olcs\Api\Domain\CommandHandler\Template\SendTestEmail as SendTestEmailHandler;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository\Template as TemplateRepo;
use Dvsa\Olcs\Api\Entity\Template\Template as TemplateEntity;
use Dvsa\Olcs\Api\Service\Template\TwigRenderer;
use Dvsa\Olcs\Email\Service\NotifyTestMailer;
use Dvsa\Olcs\Email\Transport\GovUkNotifyTransport;
use Dvsa\Olcs\Transfer\Command\Template\SendTestEmail as SendTestEmailCmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;
use Symfony\Component\Mime\Email as SymfonyEmail;

class SendTestEmailTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new SendTestEmailHandler();
        $this->mockRepo('Template', TemplateRepo::class);

        $this->mockedSmServices = [
            'TemplateTwigRenderer' => m::mock(TwigRenderer::class),
            NotifyTestMailer::class => m::mock(NotifyTestMailer::class),
            'config' => [
                'email' => [
                    'from_email' => 'no-reply@example.com',
                    'from_name' => 'OLCS',
                    'notify' => [
                        'passthrough_templates' => [
                            'en_GB' => 'uuid-en',
                            'cy_GB' => 'uuid-cy',
                        ],
                    ],
                ],
            ],
        ];

        parent::setUp();
    }

    public function testThrowsWhenNotifyTestMailerDisabled(): void
    {
        $this->mockedSmServices[NotifyTestMailer::class]
            ->shouldReceive('isEnabled')->andReturn(false);

        $command = SendTestEmailCmd::create(['id' => 42, 'recipient' => 'user@example.com']);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('NotifyTestMailer is not configured');

        $this->sut->handleCommand($command);
    }

    public function testThrowsWhenTemplateIsNotMarkdown(): void
    {
        $this->mockedSmServices[NotifyTestMailer::class]
            ->shouldReceive('isEnabled')->andReturn(true);

        $template = m::mock(TemplateEntity::class);
        $template->shouldReceive('getFormat')->andReturn('html');

        $this->repoMap['Template']
            ->shouldReceive('fetchUsingId')->andReturn($template);

        $command = SendTestEmailCmd::create(['id' => 42, 'recipient' => 'user@example.com']);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Only `format=md` templates can be sent');

        $this->sut->handleCommand($command);
    }

    public function testThrowsWhenPassthroughUuidMissingForLocale(): void
    {
        $this->mockedSmServices[NotifyTestMailer::class]
            ->shouldReceive('isEnabled')->andReturn(true);

        $template = m::mock(TemplateEntity::class);
        $template->shouldReceive('getFormat')->andReturn('md');
        $template->shouldReceive('getLocale')->andReturn('en_CY');  // not in passthrough map

        $this->repoMap['Template']
            ->shouldReceive('fetchUsingId')->andReturn($template);

        $command = SendTestEmailCmd::create(['id' => 42, 'recipient' => 'user@example.com']);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('No passthrough Notify template UUID configured for locale "en_CY"');

        $this->sut->handleCommand($command);
    }

    public function testSendsWithRenderedBodyAndStampsNotifyPayloadHeader(): void
    {
        $this->mockedSmServices[NotifyTestMailer::class]
            ->shouldReceive('isEnabled')->andReturn(true);

        $template = m::mock(TemplateEntity::class);
        $template->shouldReceive('getFormat')->andReturn('md');
        $template->shouldReceive('getLocale')->andReturn('en_GB');
        $template->shouldReceive('getDecodedTestData')->andReturn(['Default' => ['name' => 'Andy']]);
        $template->shouldReceive('getSource')->andReturn('Hi {{ name }}');
        $template->shouldReceive('getDescription')->andReturn('Greeting');
        $template->shouldReceive('getId')->andReturn(99);
        $template->shouldReceive('getName')->andReturn('greeting');

        $this->repoMap['Template']
            ->shouldReceive('fetchUsingId')->andReturn($template);

        $this->mockedSmServices['TemplateTwigRenderer']
            ->shouldReceive('renderString')->with('Hi {{ name }}', ['name' => 'Andy'])
            ->andReturn('Hi Andy');

        $sentEmail = null;
        $this->mockedSmServices[NotifyTestMailer::class]
            ->shouldReceive('send')->once()
            ->andReturnUsing(function (SymfonyEmail $e) use (&$sentEmail) {
                $sentEmail = $e;
            });

        $command = SendTestEmailCmd::create(['id' => 99, 'recipient' => 'user@example.com']);

        $result = $this->sut->handleCommand($command);

        $this->assertNotNull($sentEmail);
        $this->assertSame('Hi Andy', $sentEmail->getTextBody());
        $this->assertStringContainsString('[TEST] Greeting', $sentEmail->getSubject());
        $this->assertSame('user@example.com', $sentEmail->getTo()[0]->getAddress());

        $header = $sentEmail->getHeaders()->get(GovUkNotifyTransport::PAYLOAD_HEADER);
        $this->assertNotNull($header, 'Notify payload header must be set so the transport can use it');
        $payload = json_decode($header->getBodyAsString(), true);
        $this->assertSame('uuid-en', $payload['templateKey']);
        $this->assertSame('Hi Andy', $payload['markdownBody']);
        $this->assertSame('en_GB', $payload['locale']);

        $this->assertStringContainsString('Test email sent to user@example.com', $result->getMessages()[0]);
    }
}
