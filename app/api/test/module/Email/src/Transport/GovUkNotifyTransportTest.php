<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Email\Transport;

use Alphagov\Notifications\Client as NotifyClient;
use Alphagov\Notifications\Exception\NotifyException;
use Dvsa\Olcs\Email\Exception\EmailNotSentException;
use Dvsa\Olcs\Email\Transport\GovUkNotifyTransport;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Logging\Log\Logger;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email as SymfonyEmail;

class GovUkNotifyTransportTest extends MockeryTestCase
{
    private const EN_TEMPLATE_ID = '11111111-2222-3333-4444-555555555555';
    private const CY_TEMPLATE_ID = 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee';

    /** @var NotifyClient&\Mockery\MockInterface */
    private $notifyClient;

    private GovUkNotifyTransport $sut;

    public function setUp(): void
    {
        $logger = new \Dvsa\OlcsTest\SafeLogger();
        $logger->addWriter(new \Laminas\Log\Writer\Mock());
        Logger::setLogger($logger);

        $this->notifyClient = m::mock(NotifyClient::class);
        $this->sut = new GovUkNotifyTransport(
            $this->notifyClient,
            [
                GovUkNotifyTransport::LOCALE_EN_GB => self::EN_TEMPLATE_ID,
                'cy_GB' => self::CY_TEMPLATE_ID,
            ]
        );
    }

    public function testSendsEmailViaNotify(): void
    {
        $email = $this->buildEmail([
            'locale' => 'en_GB',
            'personalisation' => ['foo' => 'bar'],
            'markdownBody' => 'Hello **world**',
        ]);

        $this->notifyClient->shouldReceive('sendEmail')
            ->once()
            ->withArgs(function ($to, $templateId, $personalisation, $ref, $replyToId) {
                $this->assertSame('user@example.com', $to);
                $this->assertSame(self::EN_TEMPLATE_ID, $templateId);
                $this->assertSame('Subj', $personalisation['subject']);
                $this->assertSame('Hello **world**', $personalisation['body']);
                $this->assertSame('bar', $personalisation['foo']);
                return true;
            })
            ->andReturn(['id' => 'notify-msg-id-1']);

        $this->sut->send($email);
        $this->assertFalse($email->getHeaders()->has(GovUkNotifyTransport::PAYLOAD_HEADER));
    }

    public function testUsesWelshTemplateForCyLocale(): void
    {
        $email = $this->buildEmail(['locale' => 'cy_GB', 'markdownBody' => 'Helo']);

        $this->notifyClient->shouldReceive('sendEmail')
            ->once()
            ->withArgs(function ($to, $templateId) {
                $this->assertSame(self::CY_TEMPLATE_ID, $templateId);
                return true;
            })
            ->andReturn(['id' => 'notify-cy']);

        $this->sut->send($email);
    }

    public function testRetryableOn429(): void
    {
        $email = $this->buildEmail(['markdownBody' => 'x']);
        $this->notifyClient->shouldReceive('sendEmail')->andThrow(new NotifyException('rate limited', 429));

        $this->expectException(EmailNotSentException::class);
        try {
            $this->sut->send($email);
        } catch (EmailNotSentException $e) {
            $this->assertNotInstanceOf(\DomainException::class, $e->getPrevious());
            throw $e;
        }
    }

    public function testPermanentOn4xx(): void
    {
        $email = $this->buildEmail(['markdownBody' => 'x']);
        $this->notifyClient->shouldReceive('sendEmail')->andThrow(new NotifyException('bad request', 400));

        $this->expectException(EmailNotSentException::class);
        try {
            $this->sut->send($email);
        } catch (EmailNotSentException $e) {
            $this->assertInstanceOf(\DomainException::class, $e->getPrevious());
            throw $e;
        }
    }

    public function testMissingPayloadHeaderFails(): void
    {
        $email = (new SymfonyEmail())
            ->from('from@example.com')
            ->to('user@example.com')
            ->subject('Subj')
            ->text('plain');

        $this->expectException(EmailNotSentException::class);
        $this->expectExceptionMessage('expected header');
        $this->sut->send($email);
    }

    public function testUnknownLocaleFails(): void
    {
        $email = $this->buildEmail(['locale' => 'fr_FR', 'markdownBody' => 'x']);

        $this->expectException(EmailNotSentException::class);
        $this->expectExceptionMessage('No Notify passthrough template configured for locale "fr_FR"');
        $this->sut->send($email);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function buildEmail(array $payload): SymfonyEmail
    {
        $email = (new SymfonyEmail())
            ->from(new Address('from@example.com'))
            ->to(new Address('user@example.com'))
            ->subject('Subj')
            ->text('plain body');

        $email->getHeaders()->addTextHeader(
            GovUkNotifyTransport::PAYLOAD_HEADER,
            json_encode($payload, JSON_THROW_ON_ERROR),
        );

        return $email;
    }
}
