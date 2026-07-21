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

final class GovUkNotifyTransportTest extends MockeryTestCase
{
    private const string EN_TEMPLATE_ID = '11111111-2222-3333-4444-555555555555';
    private const string CY_TEMPLATE_ID = 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee';

    /** @var NotifyClient&\Mockery\MockInterface */
    private $notifyClient;

    private GovUkNotifyTransport $sut;

    #[\Override]
    public function setUp(): void
    {
        Logger::setLogger(new \Psr\Log\NullLogger());

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

        // Symfony's AbstractTransport::send() clones the message before doSend(), so the
        // internal PAYLOAD_HEADER is consumed on the clone and the caller's original $email is
        // left untouched. The Notify API call (asserted via withArgs above) is the behaviour of
        // record; header stripping on the sent message is covered by DevNotifyTransportTest.
        $this->assertTrue($email->getHeaders()->has(GovUkNotifyTransport::PAYLOAD_HEADER));
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
        $email = new SymfonyEmail()
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

    public function testFansOutOneSendPerRecipient(): void
    {
        // To + Cc + Bcc — SMTP delivered to all four; Notify has no cc/bcc, so it's one send each.
        $email = $this->buildMultiRecipientEmail(
            ['a@example.com', 'b@example.com'],
            ['c@example.com'],
            ['d@example.com'],
        );

        $recipients = [];
        $this->notifyClient->shouldReceive('sendEmail')
            ->times(4)
            ->andReturnUsing(function ($to) use (&$recipients) {
                $recipients[] = $to;
                return ['id' => 'x'];
            });

        $this->sut->send($email);

        sort($recipients);
        $this->assertSame(['a@example.com', 'b@example.com', 'c@example.com', 'd@example.com'], $recipients);
    }

    public function testDedupesRecipientsCaseInsensitively(): void
    {
        // Same mailbox in To and Bcc (different case) ⇒ a single Notify send, not two.
        $email = $this->buildMultiRecipientEmail(['user@example.com'], [], ['USER@example.com']);

        $this->notifyClient->shouldReceive('sendEmail')->once()->andReturn(['id' => 'x']);

        $this->sut->send($email);
    }

    public function testPermanentlyFailedRecipientIsSkippedNotFatal(): void
    {
        // One bad address (permanent 400) must not fail the batch; the good recipient still gets it.
        $email = $this->buildMultiRecipientEmail(['bad@example.com'], [], ['good@example.com']);

        $this->notifyClient->shouldReceive('sendEmail')
            ->with('bad@example.com', m::any(), m::any(), m::any(), m::any())
            ->once()->andThrow(new NotifyException('bad request', 400));
        $this->notifyClient->shouldReceive('sendEmail')
            ->with('good@example.com', m::any(), m::any(), m::any(), m::any())
            ->once()->andReturn(['id' => 'ok']);

        $this->sut->send($email);

        $this->addToAssertionCount(1); // reached here ⇒ no exception thrown
    }

    public function testTransientFailureRetriesWholeMessage(): void
    {
        $email = $this->buildMultiRecipientEmail(['a@example.com'], [], ['b@example.com']);

        $this->notifyClient->shouldReceive('sendEmail')
            ->with('a@example.com', m::any(), m::any(), m::any(), m::any())
            ->andThrow(new NotifyException('rate limited', 429));
        $this->notifyClient->shouldReceive('sendEmail')
            ->with('b@example.com', m::any(), m::any(), m::any(), m::any())
            ->andReturn(['id' => 'ok']);

        $this->expectException(EmailNotSentException::class);
        try {
            $this->sut->send($email);
        } catch (EmailNotSentException $e) {
            // Retryable — not a permanent DomainException.
            $this->assertNotInstanceOf(\DomainException::class, $e->getPrevious());
            throw $e;
        }
    }

    public function testAllRecipientsPermanentlyFailedIsFatal(): void
    {
        $email = $this->buildMultiRecipientEmail(['x@example.com', 'y@example.com']);

        $this->notifyClient->shouldReceive('sendEmail')->andThrow(new NotifyException('bad', 400));

        $this->expectException(EmailNotSentException::class);
        try {
            $this->sut->send($email);
        } catch (EmailNotSentException $e) {
            // Total non-delivery ⇒ permanent (DLQ) so ops sees it.
            $this->assertInstanceOf(\DomainException::class, $e->getPrevious());
            throw $e;
        }
    }

    /**
     * @param list<string> $to
     * @param list<string> $cc
     * @param list<string> $bcc
     */
    private function buildMultiRecipientEmail(array $to, array $cc = [], array $bcc = []): SymfonyEmail
    {
        $email = new SymfonyEmail()
            ->from(new Address('from@example.com'))
            ->subject('Subj')
            ->text('plain body');

        $toAddresses = array_map(static fn (string $a): Address => new Address($a), $to);
        $email->to(...$toAddresses);

        if ($cc !== []) {
            $email->cc(...array_map(static fn (string $a): Address => new Address($a), $cc));
        }
        if ($bcc !== []) {
            $email->bcc(...array_map(static fn (string $a): Address => new Address($a), $bcc));
        }

        $email->getHeaders()->addTextHeader(
            GovUkNotifyTransport::PAYLOAD_HEADER,
            json_encode(['markdownBody' => 'x'], JSON_THROW_ON_ERROR),
        );

        return $email;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function buildEmail(array $payload): SymfonyEmail
    {
        $email = new SymfonyEmail()
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
