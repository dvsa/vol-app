<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Sms;

use Alphagov\Notifications\Exception\NotifyException;
use Dvsa\Olcs\Api\Domain\Command\Sms\SendSms as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\Sms\SendSms;
use Dvsa\Olcs\Api\Domain\Exception\NotReadyException;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Notify\NotifyMessage;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Service\Sms\SmsConfig;
use Dvsa\Olcs\Api\Service\Sms\SmsGate;
use Dvsa\Olcs\Api\Service\Sms\SmsTransportInterface;
use Dvsa\Olcs\Api\Service\Template\PlainTextTwigRendererFactory;
use Dvsa\Olcs\Api\Service\Template\TwigRenderer;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;
use Twig\Error\LoaderError;

#[\PHPUnit\Framework\Attributes\CoversClass(SendSms::class)]
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
final class SendSmsTest extends AbstractCommandHandlerTestCase
{
    private const int ID = 555;
    private const string NUMBER = '+447700900123';
    private const string TEMPLATE_ID = 'tmpl-uuid';
    private const string REFERENCE = 'dev-sms-555';

    private $gate;
    private $transport;
    private $twig;
    private $messageRepo;
    private \DateTimeImmutable $now;
    private SmsConfig $config;

    public function setUp(): void
    {
        $this->now = new \DateTimeImmutable('2026-11-05 10:30:00', new \DateTimeZone('Europe/London'));
        $this->config = new SmsConfig('key', false, null, 1025, self::TEMPLATE_ID, 'dev');

        $this->sut = m::mock(SendSms::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $this->sut->shouldReceive('now')->andReturnUsing(fn() => $this->now);

        $this->messageRepo = $this->mockRepo('NotifyMessage', Repository\NotifyMessage::class);
        $this->messageRepo->shouldReceive('hasOtherSmsSentToRecipientBetween')->andReturn(false)->byDefault();
        $this->gate = m::mock(SmsGate::class);
        $this->transport = m::mock(SmsTransportInterface::class);
        $this->twig = m::mock(TwigRenderer::class);

        $this->refData = [
            NotifyMessage::STATUS_CANCELLED,
            NotifyMessage::STATUS_REJECTED,
            NotifyMessage::STATUS_FAILED,
            NotifyMessage::STATUS_CREATED,
            NotifyMessage::STATUS_QUEUED,
        ];

        $this->mockedSmServices[SmsGate::class] = $this->gate;
        $this->mockedSmServices[SmsConfig::class] = $this->config;
        $this->mockedSmServices[SmsTransportInterface::class] = $this->transport;
        $this->mockedSmServices['TemplatePlainTextTwigRenderer'] = $this->twig;

        parent::setUp();
    }

    private function message(bool $welsh = false, int $attempts = 0): NotifyMessage
    {
        $org = m::mock(Organisation::class)->makePartial();
        $org->setName('Acme Haulage');
        $licence = m::mock(Licence::class)->makePartial();
        $licence->setTranslateToWelsh($welsh ? 'Y' : 'N');

        $status = $this->refData[NotifyMessage::STATUS_QUEUED];
        $m = new NotifyMessage($status, $status, self::NUMBER, $this->now);
        $m->setId(self::ID);
        $m->setOrganisation($org);
        $m->setLicence($licence);
        $m->setTemplateName('letter-issued-sms');
        $m->setAttempts($attempts);
        $this->messageRepo->shouldReceive('fetchById')->with(self::ID)->andReturn($m);

        return $m;
    }

    private function handle(): mixed
    {
        return $this->sut->handleCommand(Cmd::create(['id' => self::ID]));
    }

    public function testGateOffCancels(): void
    {
        $m = $this->message();
        $this->gate->shouldReceive('isEnabled')->andReturn(false);
        $this->transport->shouldNotReceive('send');
        $this->messageRepo->shouldReceive('save')->once()->with($m);

        $this->handle();

        $this->assertSame(NotifyMessage::STATUS_CANCELLED, $m->getStatus()->getId());
    }

    public function testOutsideWindowThrowsNotReady(): void
    {
        $this->now = new \DateTimeImmutable('2026-11-05 21:00:00', new \DateTimeZone('Europe/London'));
        $this->message();
        $this->gate->shouldReceive('isEnabled')->andReturn(true);
        $this->transport->shouldNotReceive('send');
        $this->messageRepo->shouldNotReceive('save');

        try {
            $this->handle();
            $this->fail('expected NotReadyException');
        } catch (NotReadyException $e) {
            // 21:00 to 08:00 next day
            $this->assertSame(11 * 3600, $e->getRetryAfter());
        }
    }

    public function testSendsRenderedBodyWithReference(): void
    {
        $m = $this->message();
        $this->gate->shouldReceive('isEnabled')->andReturn(true);
        $this->twig->shouldReceive('render')
            ->once()
            ->with('en_GB/sms/letter-issued-sms', ['name' => 'Acme Haulage', 'dayOfWeek' => 'Thursday'])
            ->andReturn('Hello Acme');
        $this->transport->shouldReceive('send')
            ->once()
            ->with(self::NUMBER, self::TEMPLATE_ID, 'Hello Acme', self::REFERENCE)
            ->andReturn('notify-1');
        $this->messageRepo->shouldReceive('save')->twice()->with($m);

        $this->handle();

        $this->assertSame('notify-1', $m->getNotifyId());
        $this->assertSame(NotifyMessage::STATUS_CREATED, $m->getStatus()->getId());
        $this->assertSame(1, $m->getAttempts());
        $this->assertSame(self::TEMPLATE_ID, $m->getNotifyTemplateId());
        $this->assertInstanceOf(\DateTime::class, $m->getSentOn());
        $this->assertNotInstanceOf(\DateTimeImmutable::class, $m->getSentOn());
        $this->assertInstanceOf(\DateTime::class, $m->getStatusUpdatedOn());
    }

    public function testOrganisationNameIsSentLiterally(): void
    {
        $m = $this->message();
        $m->getOrganisation()->setName("Smith & Sons' <Haulage>");
        $this->gate->shouldReceive('isEnabled')->andReturn(true);

        // the real plain text renderer over the seeded template
        $loader = new \Twig\Loader\ArrayLoader(
            ['en_GB/sms/letter-issued-sms' => "Hey {{ name }}, I'm trying out Notify. Today is {{ dayOfWeek }}"]
        );
        $container = m::mock(\Psr\Container\ContainerInterface::class);
        $container->shouldReceive('get')->with('TemplateDatabaseTwigLoader')->andReturn($loader);
        $real = (new PlainTextTwigRendererFactory())($container, 'TemplatePlainTextTwigRenderer');
        $this->twig->shouldReceive('render')->andReturnUsing(fn ($path, $vars) => $real->render($path, $vars));

        $this->transport->shouldReceive('send')
            ->once()
            ->with(
                self::NUMBER,
                self::TEMPLATE_ID,
                "Hey Smith & Sons' <Haulage>, I'm trying out Notify. Today is Thursday",
                self::REFERENCE
            )
            ->andReturn('notify-1');
        $this->messageRepo->shouldReceive('save')->twice()->with($m);

        $this->handle();
    }

    public function testWelshLicenceUsesCyTemplateWhenPresent(): void
    {
        $this->message(true);
        $this->gate->shouldReceive('isEnabled')->andReturn(true);
        $this->twig->shouldReceive('render')->once()->with('cy_GB/sms/letter-issued-sms', m::type('array'))->andReturn('Helo');
        $this->transport->shouldReceive('send')->once()->with(m::any(), m::any(), 'Helo', m::any())->andReturn(null);
        $this->messageRepo->shouldReceive('save')->twice();

        $this->handle();
    }

    public function testFallsBackToEnglish(): void
    {
        $this->message(true);
        $this->gate->shouldReceive('isEnabled')->andReturn(true);
        $this->twig->shouldReceive('render')->once()->with('cy_GB/sms/letter-issued-sms', m::type('array'))
            ->andThrow(new LoaderError('missing'));
        $this->twig->shouldReceive('render')->once()->with('en_GB/sms/letter-issued-sms', m::type('array'))->andReturn('Hello');
        $this->transport->shouldReceive('send')->once()->with(m::any(), m::any(), 'Hello', m::any())->andReturn(null);
        $this->messageRepo->shouldReceive('save')->twice();

        $this->handle();
    }

    public function testSendsToTheOperatorsNumber(): void
    {
        $m = $this->message();
        $this->gate->shouldReceive('isEnabled')->andReturn(true);
        $this->twig->shouldReceive('render')->andReturn('x');
        $this->transport->shouldReceive('send')->once()->with(self::NUMBER, m::any(), 'x', m::any())->andReturn('n');
        $this->messageRepo->shouldReceive('save')->twice();

        $this->handle();

        $this->assertNull($m->getRedirectedTo());
    }

    public function testPermanent400Rejected(): void
    {
        $m = $this->message();
        $this->gate->shouldReceive('isEnabled')->andReturn(true);
        $this->twig->shouldReceive('render')->andReturn('x');
        $this->transport->shouldReceive('send')->andThrow(new NotifyException('Not a UK mobile', 400));
        $this->messageRepo->shouldReceive('save')->twice()->with($m);

        $this->handle();

        $this->assertSame(NotifyMessage::STATUS_REJECTED, $m->getStatus()->getId());
        $this->assertSame('400', $m->getErrorCode());
        $this->assertSame('Not a UK mobile', $m->getErrorMessage());
        $this->assertNotInstanceOf(\DateTimeImmutable::class, $m->getCompletedOn());
        $this->assertNotNull($m->getCompletedOn());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('transientCodes')]
    public function testTransientThrowsNotReady(int $code): void
    {
        $m = $this->message();
        $this->gate->shouldReceive('isEnabled')->andReturn(true);
        $this->twig->shouldReceive('render')->andReturn('x');
        $this->transport->shouldReceive('send')->andThrow(new NotifyException('boom', $code));
        $this->messageRepo->shouldReceive('save')->twice()->with($m);

        try {
            $this->handle();
            $this->fail('expected NotReadyException');
        } catch (NotReadyException $e) {
            $this->assertSame(900, $e->getRetryAfter());
        }

        $this->assertSame(1, $m->getAttempts());
        $this->assertSame(NotifyMessage::STATUS_QUEUED, $m->getStatus()->getId());
    }

    public static function transientCodes(): array
    {
        return [[429], [500], [0]];
    }

    public function testTransientOnLastAttemptMarksFailed(): void
    {
        $m = $this->message(false, 3);
        $m->setNotifyTemplateId(self::TEMPLATE_ID);
        $this->gate->shouldReceive('isEnabled')->andReturn(true);
        $this->twig->shouldReceive('render')->andReturn('x');
        $this->transport->shouldReceive('findByReference')->once()->andReturn(null);
        $this->transport->shouldReceive('send')->andThrow(new NotifyException('boom', 503));
        $this->messageRepo->shouldReceive('save')->twice()->with($m);

        $this->handle();

        $this->assertSame(4, $m->getAttempts());
        $this->assertSame(NotifyMessage::STATUS_FAILED, $m->getStatus()->getId());
        $this->assertSame('503', $m->getErrorCode());
    }

    public function testAlreadyProcessedIsSkipped(): void
    {
        $m = $this->message();
        $m->setStatus($this->refData[NotifyMessage::STATUS_CREATED]);
        $this->gate->shouldReceive('isEnabled')->andReturn(true);
        $this->transport->shouldNotReceive('send');
        $this->messageRepo->shouldNotReceive('save');

        $this->handle();
    }

    public function testTransientRetryNeverLandsOutsideWindow(): void
    {
        $this->now = new \DateTimeImmutable('2026-11-05 19:50:00', new \DateTimeZone('Europe/London'));
        $this->message();
        $this->gate->shouldReceive('isEnabled')->andReturn(true);
        $this->twig->shouldReceive('render')->andReturn('x');
        $this->transport->shouldReceive('send')->andThrow(new NotifyException('boom', 503));
        $this->messageRepo->shouldReceive('save')->twice();

        try {
            $this->handle();
            $this->fail('expected NotReadyException');
        } catch (NotReadyException $e) {
            // 19:50 to 08:00 next day
            $this->assertSame(12 * 3600 + 10 * 60, $e->getRetryAfter());
        }
    }

    public function testMissingTemplateMarksFailed(): void
    {
        $m = $this->message();
        $this->gate->shouldReceive('isEnabled')->andReturn(true);
        $this->twig->shouldReceive('render')->andThrow(new LoaderError('no such template'));
        $this->transport->shouldNotReceive('send');
        $this->messageRepo->shouldReceive('save')->once()->with($m);

        $this->handle();

        $this->assertSame(NotifyMessage::STATUS_FAILED, $m->getStatus()->getId());
        $this->assertSame('no such template', $m->getErrorMessage());
        $this->assertNotInstanceOf(\DateTimeImmutable::class, $m->getCompletedOn());
    }

    public function testTransportErrorMarksFailedWithoutRethrow(): void
    {
        $m = $this->message();
        $this->gate->shouldReceive('isEnabled')->andReturn(true);
        $this->twig->shouldReceive('render')->andReturn('x');
        $this->transport->shouldReceive('send')->once()->andThrow(new \RuntimeException('socket closed'));
        $this->messageRepo->shouldReceive('save')->with($m);

        $this->handle();

        $this->assertSame(NotifyMessage::STATUS_FAILED, $m->getStatus()->getId());
        $this->assertSame('RuntimeException: socket closed', $m->getErrorMessage());
        $this->assertNotNull($m->getCompletedOn());
        $this->assertNull($m->getNotifyId());
    }

    public function testAttemptIsSavedBeforeSending(): void
    {
        $m = $this->message();
        $this->gate->shouldReceive('isEnabled')->andReturn(true);
        $this->twig->shouldReceive('render')->andReturn('x');
        $savedAttempts = [];
        $this->messageRepo->shouldReceive('save')->with($m)->andReturnUsing(function () use ($m, &$savedAttempts) {
            $savedAttempts[] = [$m->getAttempts(), $m->getStatus()->getId()];
        });
        $this->transport->shouldReceive('send')->once()->andReturnUsing(function () use (&$savedAttempts) {
            // a crash after this point must leave attempts > 0 in the DB
            $this->assertSame([[1, NotifyMessage::STATUS_QUEUED]], $savedAttempts);

            return 'notify-1';
        });

        $this->handle();

        $this->assertSame([1, NotifyMessage::STATUS_CREATED], $savedAttempts[1]);
    }

    public function testOutsideWindowDoesNotUseASendAttempt(): void
    {
        $this->now = new \DateTimeImmutable('2026-11-05 21:00:00', new \DateTimeZone('Europe/London'));
        $m = $this->message(false, 1);
        $this->gate->shouldReceive('isEnabled')->andReturn(true);
        $this->transport->shouldNotReceive('send');
        $this->messageRepo->shouldNotReceive('save');

        $this->expectException(NotReadyException::class);

        try {
            $this->handle();
        } finally {
            $this->assertSame(1, $m->getAttempts());
            $this->assertSame(NotifyMessage::STATUS_QUEUED, $m->getStatus()->getId());
        }
    }

    public function testOutsideWindowMoreThan12HoursAfterRequestMarksFailed(): void
    {
        $this->now = new \DateTimeImmutable('2026-11-05 21:00:00', new \DateTimeZone('Europe/London'));
        $m = $this->message(false, 1);
        $m->setRequestedOn(new \DateTime('2026-11-05 08:59:59', new \DateTimeZone('Europe/London')));
        $this->gate->shouldReceive('isEnabled')->andReturn(true);
        $this->transport->shouldNotReceive('send');
        $this->messageRepo->shouldReceive('save')->once()->with($m);

        $this->handle();

        $this->assertSame(1, $m->getAttempts());
        $this->assertSame(NotifyMessage::STATUS_FAILED, $m->getStatus()->getId());
        $this->assertNotNull($m->getCompletedOn());
        $this->assertNotNull($m->getErrorMessage());
    }

    public function testOutsideWindowWithin12HoursOfRequestStillDefers(): void
    {
        $this->now = new \DateTimeImmutable('2026-11-05 21:00:00', new \DateTimeZone('Europe/London'));
        $m = $this->message();
        $m->setRequestedOn(new \DateTime('2026-11-05 09:00:00', new \DateTimeZone('Europe/London')));
        $this->gate->shouldReceive('isEnabled')->andReturn(true);
        $this->transport->shouldNotReceive('send');

        $this->expectException(NotReadyException::class);
        $this->handle();
    }

    public function testFirstSendAfterAWindowDeferralDoesNotLookUp(): void
    {
        // deferred overnight: nothing was ever sent, so there is nothing to look up
        $m = $this->message();
        $this->gate->shouldReceive('isEnabled')->andReturn(true);
        $this->twig->shouldReceive('render')->andReturn('x');
        $this->transport->shouldNotReceive('findByReference');
        $this->transport->shouldReceive('send')->once()->andReturn('notify-1');
        $this->messageRepo->shouldReceive('save');

        $this->handle();

        $this->assertSame(1, $m->getAttempts());
    }

    public function testSuccessClearsEarlierError(): void
    {
        $m = $this->message(false, 1);
        $m->setNotifyTemplateId(self::TEMPLATE_ID);
        $m->setErrorCode('503');
        $m->setErrorMessage('boom');
        $this->gate->shouldReceive('isEnabled')->andReturn(true);
        $this->twig->shouldReceive('render')->andReturn('x');
        $this->transport->shouldReceive('findByReference')->andReturn(null);
        $this->transport->shouldReceive('send')->once()->andReturn('notify-1');
        $this->messageRepo->shouldReceive('save');

        $this->handle();

        $this->assertSame(NotifyMessage::STATUS_CREATED, $m->getStatus()->getId());
        $this->assertNull($m->getErrorCode());
        $this->assertNull($m->getErrorMessage());
    }

    public function testRecordingAnEarlierSendClearsTheError(): void
    {
        $m = $this->message(false, 1);
        $m->setNotifyTemplateId(self::TEMPLATE_ID);
        $m->setErrorCode('0');
        $m->setErrorMessage('timed out');
        $this->gate->shouldReceive('isEnabled')->andReturn(true);
        $this->twig->shouldReceive('render')->andReturn('x');
        $this->transport->shouldReceive('findByReference')->andReturn('notify-1');
        $this->transport->shouldNotReceive('send');
        $this->messageRepo->shouldReceive('save');

        $this->handle();

        $this->assertNull($m->getErrorCode());
        $this->assertNull($m->getErrorMessage());
    }

    public function testFirstAttemptDoesNotLookUp(): void
    {
        $this->message();
        $this->gate->shouldReceive('isEnabled')->andReturn(true);
        $this->twig->shouldReceive('render')->andReturn('x');
        $this->transport->shouldNotReceive('findByReference');
        $this->transport->shouldReceive('send')->once()->andReturn('notify-1');
        $this->messageRepo->shouldReceive('save');

        $this->handle();
    }

    public function testRetryRecordsAnEarlierAcceptedSendInsteadOfSendingAgain(): void
    {
        $m = $this->message();
        $this->gate->shouldReceive('isEnabled')->andReturn(true);
        $this->twig->shouldReceive('render')->andReturn('x');
        $this->messageRepo->shouldReceive('save')->with($m);
        // attempt 1: Notify took it but the response was lost
        $this->transport->shouldReceive('send')->once()->andThrow(new NotifyException('timed out', 0));

        try {
            $this->handle();
            $this->fail('expected NotReadyException');
        } catch (NotReadyException) {
        }

        // attempt 2: Notify already has our reference
        $this->transport->shouldReceive('findByReference')->once()->with(self::REFERENCE)->andReturn('notify-1');

        $this->handle();

        $this->assertSame('notify-1', $m->getNotifyId());
        $this->assertSame(NotifyMessage::STATUS_CREATED, $m->getStatus()->getId());
        $this->assertNotNull($m->getSentOn());
        $this->assertSame(2, $m->getAttempts());
    }

    public function testRetrySendsWhenNotifyHasNothing(): void
    {
        $m = $this->message(false, 1);
        $m->setNotifyTemplateId(self::TEMPLATE_ID);
        $this->gate->shouldReceive('isEnabled')->andReturn(true);
        $this->twig->shouldReceive('render')->andReturn('x');
        $this->messageRepo->shouldReceive('save')->with($m);
        $this->transport->shouldReceive('findByReference')->once()->with(self::REFERENCE)->andReturn(null)->ordered();
        $this->transport->shouldReceive('send')->once()->andReturn('notify-2')->ordered();

        $this->handle();

        $this->assertSame('notify-2', $m->getNotifyId());
        $this->assertSame(NotifyMessage::STATUS_CREATED, $m->getStatus()->getId());
    }

    public function testFailedLookupRetriesWithoutSending(): void
    {
        $m = $this->message(false, 1);
        $m->setNotifyTemplateId(self::TEMPLATE_ID);
        $this->gate->shouldReceive('isEnabled')->andReturn(true);
        $this->twig->shouldReceive('render')->andReturn('x');
        $this->messageRepo->shouldReceive('save')->once()->with($m);
        $this->transport->shouldReceive('findByReference')->andThrow(new NotifyException('boom', 503));
        $this->transport->shouldNotReceive('send');

        try {
            $this->handle();
            $this->fail('expected NotReadyException');
        } catch (NotReadyException $e) {
            $this->assertSame(900, $e->getRetryAfter());
        }

        $this->assertSame(2, $m->getAttempts());
        $this->assertSame(NotifyMessage::STATUS_QUEUED, $m->getStatus()->getId());
    }

    public function testFailedLookupOnLastAttemptMarksFailed(): void
    {
        $m = $this->message(false, 3);
        $m->setNotifyTemplateId(self::TEMPLATE_ID);
        $this->gate->shouldReceive('isEnabled')->andReturn(true);
        $this->twig->shouldReceive('render')->andReturn('x');
        $this->messageRepo->shouldReceive('save')->once()->with($m);
        $this->transport->shouldReceive('findByReference')->andThrow(new \RuntimeException('dns'));
        $this->transport->shouldNotReceive('send');

        $this->handle();

        $this->assertSame(NotifyMessage::STATUS_FAILED, $m->getStatus()->getId());
        $this->assertNotNull($m->getCompletedOn());
    }

    public function testLetterTextCancelledWhenTheNumberHadOneSentThisSmsDay(): void
    {
        $m = $this->message();
        $this->gate->shouldReceive('isEnabled')->andReturn(true);
        $this->twig->shouldReceive('render')->andReturn('x');
        $this->messageRepo->shouldReceive('hasOtherSmsSentToRecipientBetween')
            ->once()
            ->withArgs(function (int $id, string $to, string $name, \DateTimeInterface $start, \DateTimeInterface $end) {
                $london = new \DateTimeZone('Europe/London');

                // Thursday 10:30 is in the SMS day from Wednesday 20:00 to Thursday 20:00
                return $id === self::ID
                    && $to === self::NUMBER
                    && $name === 'letter-issued-sms'
                    && $start->setTimezone($london)->format('Y-m-d H:i') === '2026-11-04 20:00'
                    && $end->setTimezone($london)->format('Y-m-d H:i') === '2026-11-05 20:00';
            })
            ->andReturn(true);
        $this->transport->shouldReceive('send')->never();
        $this->messageRepo->shouldReceive('save')->with($m);

        $result = $this->handle();

        $this->assertSame(NotifyMessage::STATUS_CANCELLED, $m->getStatus()->getId());
        $this->assertNotNull($m->getCompletedOn());
        $this->assertNull($m->getSentOn());
        // never attempted, so nothing marks it as possibly sent
        $this->assertSame(0, $m->getAttempts());
        $this->assertNull($m->getNotifyTemplateId());
        $this->assertSame(['SMS cancelled, this number already had one today'], $result->getMessages());
    }

    public function testRetryNotifyHasNothingForIsCancelledWhenTheNumberHadOneSentThisSmsDay(): void
    {
        $m = $this->message(false, 1);
        $m->setNotifyTemplateId(self::TEMPLATE_ID);
        $this->gate->shouldReceive('isEnabled')->andReturn(true);
        $this->twig->shouldReceive('render')->andReturn('x');
        $this->transport->shouldReceive('findByReference')->once()->with(self::REFERENCE)->andReturnNull();
        $this->messageRepo->shouldReceive('hasOtherSmsSentToRecipientBetween')->once()->andReturn(true);
        $this->messageRepo->shouldReceive('save')->with($m);
        $this->transport->shouldReceive('send')->never();

        $this->handle();

        $this->assertSame(NotifyMessage::STATUS_CANCELLED, $m->getStatus()->getId());
        $this->assertNull($m->getSentOn());
    }

    public function testOtherKindsOfTextAreNotLimitedAtSend(): void
    {
        $m = $this->message();
        $m->setTemplateName('one-time-code-sms');
        $this->gate->shouldReceive('isEnabled')->andReturn(true);
        $this->twig->shouldReceive('render')->andReturn('x');
        $this->messageRepo->shouldReceive('hasOtherSmsSentToRecipientBetween')->never();
        $this->messageRepo->shouldReceive('save')->with($m);
        $this->transport->shouldReceive('send')->once()->andReturn('notify-1');

        $this->handle();

        $this->assertSame(NotifyMessage::STATUS_CREATED, $m->getStatus()->getId());
    }

    public function testRetryThatAlreadyWentIsRecordedRatherThanCancelled(): void
    {
        $m = $this->message(false, 1);
        $m->setNotifyTemplateId(self::TEMPLATE_ID);
        $this->gate->shouldReceive('isEnabled')->andReturn(true);
        $this->twig->shouldReceive('render')->andReturn('x');
        $this->transport->shouldReceive('findByReference')->once()->with(self::REFERENCE)->andReturn('notify-1');
        $this->messageRepo->shouldReceive('hasOtherSmsSentToRecipientBetween')->never();
        $this->messageRepo->shouldReceive('save')->with($m);
        $this->transport->shouldReceive('send')->never();

        $this->handle();

        $this->assertSame('notify-1', $m->getNotifyId());
        $this->assertSame(NotifyMessage::STATUS_CREATED, $m->getStatus()->getId());
    }
}
