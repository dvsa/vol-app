<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Sms;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Sms\QueueLetterSms as Cmd;
use Dvsa\Olcs\Api\Domain\Command\Sms\SendSms;
use Dvsa\Olcs\Api\Domain\CommandHandler\Sms\QueueLetterSms;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\Doc\DocTemplate;
use Dvsa\Olcs\Api\Entity\Doc\Document;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Notify\NotifyMessage;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\Queue\Queue;
use Dvsa\Olcs\Api\Service\Sms\SmsGate;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(QueueLetterSms::class)]
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
final class QueueLetterSmsTest extends AbstractCommandHandlerTestCase
{
    private const int DOC_ID = 7001;
    private const int LIC_ID = 8001;
    private const int MESSAGE_ID = 555;
    private const string NUMBER = '+447700900123';

    /** @var m\MockInterface */
    private $gate;
    /** @var m\MockInterface */
    private $docRepo;
    /** @var m\MockInterface */
    private $templateRepo;
    /** @var m\MockInterface */
    private $messageRepo;
    private \DateTimeImmutable $now;

    public function setUp(): void
    {
        $this->now = new \DateTimeImmutable('2026-11-05 10:30:00', new \DateTimeZone('Europe/London'));

        $this->sut = m::mock(QueueLetterSms::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $this->sut->shouldReceive('now')->andReturnUsing(fn() => $this->now);

        $this->docRepo = $this->mockRepo('Document', Repository\Document::class);
        $this->templateRepo = $this->mockRepo('DocTemplate', Repository\DocTemplate::class);
        $this->messageRepo = $this->mockRepo('NotifyMessage', Repository\NotifyMessage::class);

        $this->gate = m::mock(SmsGate::class);
        $this->mockedSmServices[SmsGate::class] = $this->gate;

        $this->refData = [NotifyMessage::CHANNEL_SMS, NotifyMessage::STATUS_QUEUED];

        parent::setUp();
    }

    private function licence(?string $number = self::NUMBER): Licence
    {
        $cd = m::mock(ContactDetails::class)->makePartial();
        $cd->shouldReceive('getPhoneContactNumber')->andReturn($number);

        $licence = m::mock(Licence::class)->makePartial();
        $licence->setId(self::LIC_ID);
        $licence->setOrganisation(m::mock(Organisation::class)->makePartial());
        $licence->setCorrespondenceCd($cd);

        return $licence;
    }

    private function document(?Licence $licence, ?string $metadata = null): Document
    {
        $document = m::mock(Document::class)->makePartial();
        $document->setId(self::DOC_ID);
        $document->setDescription('A letter');
        $document->setMetadata($metadata);
        $document->shouldReceive('getRelatedLicence')->andReturn($licence);
        $this->docRepo->shouldReceive('fetchById')->with(self::DOC_ID)->andReturn($document);

        return $document;
    }

    public function testGateOffWritesNothing(): void
    {
        $this->gate->shouldReceive('isEnabled')->andReturn(false);
        $this->docRepo->shouldNotReceive('fetchById');
        $this->messageRepo->shouldNotReceive('save');

        $this->sut->handleCommand(Cmd::create(['document' => self::DOC_ID]));
    }

    public function testSuppressFromOpTemplateSkipped(): void
    {
        $this->gate->shouldReceive('isEnabled')->andReturn(true);
        $this->document($this->licence(), json_encode(['details' => ['documentTemplate' => 12]]));

        $template = m::mock(DocTemplate::class)->makePartial();
        $template->setSuppressFromOp('Y');
        $this->templateRepo->shouldReceive('fetchById')->with(12)->andReturn($template);
        $this->messageRepo->shouldNotReceive('save');

        $this->sut->handleCommand(Cmd::create(['document' => self::DOC_ID]));
    }

    public function testNoLicenceSkipped(): void
    {
        $this->gate->shouldReceive('isEnabled')->andReturn(true);
        $this->document(null);
        $this->messageRepo->shouldNotReceive('save');

        $this->sut->handleCommand(Cmd::create(['document' => self::DOC_ID]));
    }

    public function testNoSmsNumberSkipped(): void
    {
        $this->gate->shouldReceive('isEnabled')->andReturn(true);
        $this->document($this->licence(null));
        $this->messageRepo->shouldNotReceive('save');

        $this->sut->handleCommand(Cmd::create(['document' => self::DOC_ID]));
    }

    public function testSameNumberAlreadyTextedTodaySkipped(): void
    {
        $this->gate->shouldReceive('isEnabled')->andReturn(true);
        $this->document($this->licence());
        $this->messageRepo->shouldReceive('hasSmsForRecipientBetween')
            ->once()
            ->with(self::NUMBER, m::type(\DateTimeInterface::class), m::type(\DateTimeInterface::class))
            ->andReturn(true);
        $this->messageRepo->shouldNotReceive('save');

        $this->sut->handleCommand(Cmd::create(['document' => self::DOC_ID]));
    }

    public function testQueuesRowAndQueueItem(): void
    {
        $this->gate->shouldReceive('isEnabled')->andReturn(true);
        $document = $this->document($this->licence());
        $application = m::mock(Application::class)->makePartial();
        $document->setApplication($application);
        $this->messageRepo->shouldReceive('hasSmsForRecipientBetween')->andReturn(false);

        $this->messageRepo->shouldReceive('save')->once()->andReturnUsing(
            function (NotifyMessage $message): void {
                $this->assertSame($this->refData[NotifyMessage::CHANNEL_SMS], $message->getChannel());
                $this->assertSame($this->refData[NotifyMessage::STATUS_QUEUED], $message->getStatus());
                $this->assertSame(self::NUMBER, $message->getRecipient());
                $this->assertEquals($this->now, $message->getRequestedOn());
                $this->assertSame(self::LIC_ID, $message->getLicence()->getId());
                $this->assertNotNull($message->getOrganisation());
                $this->assertSame(self::DOC_ID, $message->getDocument()->getId());
                $this->assertSame('letter-issued-sms', $message->getTemplateName());
                $this->assertSame('A letter', $message->getDescription());
                $this->assertNotNull($message->getApplication());
                $this->assertNull($message->getBusReg());
                $this->assertNull($message->getCase());
                $message->setId(self::MESSAGE_ID);
            }
        );

        $this->expectedQueueSideEffect(
            self::MESSAGE_ID,
            Queue::TYPE_SMS,
            ['commandClass' => SendSms::class, 'commandData' => ['id' => self::MESSAGE_ID]],
            new Result(),
            null
        );

        $result = $this->sut->handleCommand(Cmd::create(['document' => self::DOC_ID]));

        $this->assertSame(self::MESSAGE_ID, $result->getId('notifyMessage'));
    }

    public function testOutsideWindowSetsProcessAfter(): void
    {
        $this->now = new \DateTimeImmutable('2026-11-05 21:30:00', new \DateTimeZone('Europe/London'));
        $this->gate->shouldReceive('isEnabled')->andReturn(true);
        $this->document($this->licence());
        $this->messageRepo->shouldReceive('hasSmsForRecipientBetween')->andReturn(false);
        $this->messageRepo->shouldReceive('save')->once()->andReturnUsing(
            fn(NotifyMessage $message) => $message->setId(self::MESSAGE_ID)
        );

        $this->expectedQueueSideEffect(
            self::MESSAGE_ID,
            Queue::TYPE_SMS,
            ['commandClass' => SendSms::class, 'commandData' => ['id' => self::MESSAGE_ID]],
            new Result(),
            '2026-11-06 08:00:00'
        );

        $this->sut->handleCommand(Cmd::create(['document' => self::DOC_ID]));
    }

    public function testBstWindowIsSentToQueueAsUtc(): void
    {
        $this->now = new \DateTimeImmutable('2026-07-01 21:30:00', new \DateTimeZone('Europe/London'));
        $this->gate->shouldReceive('isEnabled')->andReturn(true);
        $this->document($this->licence());
        $this->messageRepo->shouldReceive('hasSmsForRecipientBetween')->andReturn(false);
        $this->messageRepo->shouldReceive('save')->once()->andReturnUsing(
            fn(NotifyMessage $message) => $message->setId(self::MESSAGE_ID)
        );

        $this->expectedQueueSideEffect(
            self::MESSAGE_ID,
            Queue::TYPE_SMS,
            ['commandClass' => SendSms::class, 'commandData' => ['id' => self::MESSAGE_ID]],
            new Result(),
            '2026-07-02 07:00:00'
        );

        $this->sut->handleCommand(Cmd::create(['document' => self::DOC_ID]));
    }

    /**
     * The dedupe window is the SMS day (20:00 to 20:00 London) that the text will actually go out in.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('dpSmsDay')]
    public function testDedupeWindowIsTheSmsDay(string $letterAt, string $expectedStartUtc, string $expectedEndUtc): void
    {
        $this->now = new \DateTimeImmutable($letterAt, new \DateTimeZone('Europe/London'));
        $this->gate->shouldReceive('isEnabled')->andReturn(true);
        $this->document($this->licence());
        $this->messageRepo->shouldReceive('hasSmsForRecipientBetween')
            ->once()
            ->andReturnUsing(
                function (string $recipient, \DateTimeInterface $start, \DateTimeInterface $end) use ($expectedStartUtc, $expectedEndUtc): bool {
                    $utc = new \DateTimeZone('UTC');
                    $this->assertSame($expectedStartUtc, \DateTimeImmutable::createFromInterface($start)->setTimezone($utc)->format('Y-m-d H:i'));
                    $this->assertSame($expectedEndUtc, \DateTimeImmutable::createFromInterface($end)->setTimezone($utc)->format('Y-m-d H:i'));
                    return true;
                }
            );
        $this->messageRepo->shouldNotReceive('save');

        $this->sut->handleCommand(Cmd::create(['document' => self::DOC_ID]));
    }

    public static function dpSmsDay(): \Iterator
    {
        // 2026-11-02 is a Monday, GMT
        yield 'Mon 21:00 goes Tue 08:00' => ['2026-11-02 21:00', '2026-11-02 20:00', '2026-11-03 20:00'];
        yield 'Tue 10:00' => ['2026-11-03 10:00', '2026-11-02 20:00', '2026-11-03 20:00'];
        yield 'Tue 19:59' => ['2026-11-03 19:59', '2026-11-02 20:00', '2026-11-03 20:00'];
        yield 'Tue 20:01 goes Wed 08:00' => ['2026-11-03 20:01', '2026-11-03 20:00', '2026-11-04 20:00'];
        yield 'Wed 03:00 goes Wed 08:00' => ['2026-11-04 03:00', '2026-11-03 20:00', '2026-11-04 20:00'];
        // 2026-06-02 is a Tuesday, BST, so London 20:00 is 19:00 UTC
        yield 'BST Tue 10:00' => ['2026-06-02 10:00', '2026-06-01 19:00', '2026-06-02 19:00'];
        yield 'BST Tue 20:01 goes Wed 08:00' => ['2026-06-02 20:01', '2026-06-02 19:00', '2026-06-03 19:00'];
    }

    /**
     * Two letters for the same number, run through the handler against an in-memory store.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('dpTwoLetters')]
    public function testTwoLettersSendExpectedNumberOfTexts(string $first, string $second, int $expectedTexts): void
    {
        $this->gate->shouldReceive('isEnabled')->andReturn(true);
        $this->document($this->licence());

        /** @var \DateTimeInterface[] $requested */
        $requested = [];
        $this->messageRepo->shouldReceive('hasSmsForRecipientBetween')->andReturnUsing(
            function (string $recipient, \DateTimeInterface $start, \DateTimeInterface $end) use (&$requested): bool {
                foreach ($requested as $at) {
                    if ($at >= $start && $at < $end) {
                        return true;
                    }
                }
                return false;
            }
        );
        $this->messageRepo->shouldReceive('save')->andReturnUsing(
            function (NotifyMessage $message) use (&$requested): void {
                $requested[] = $message->getRequestedOn();
                $message->setId(self::MESSAGE_ID + count($requested));
            }
        );
        $this->commandHandler->shouldReceive('handleCommand')->andReturn(new Result());

        foreach ([$first, $second] as $at) {
            $this->now = new \DateTimeImmutable($at, new \DateTimeZone('Europe/London'));
            $this->sut->handleCommand(Cmd::create(['document' => self::DOC_ID]));
        }

        $this->assertCount($expectedTexts, $requested);
    }

    public static function dpTwoLetters(): \Iterator
    {
        yield 'Mon 21:00 then Tue 10:00' => ['2026-11-02 21:00', '2026-11-03 10:00', 1];
        yield 'Tue 10:00 then Tue 19:59' => ['2026-11-03 10:00', '2026-11-03 19:59', 1];
        yield 'Tue 19:59 then Tue 20:01' => ['2026-11-03 19:59', '2026-11-03 20:01', 2];
        yield 'BST Mon 21:00 then Tue 10:00' => ['2026-06-01 21:00', '2026-06-02 10:00', 1];
        yield 'BST Tue 19:59 then Tue 20:01' => ['2026-06-02 19:59', '2026-06-02 20:01', 2];
    }

    public function testMissingTemplateSkipsSms(): void
    {
        $this->gate->shouldReceive('isEnabled')->andReturn(true);
        $this->document($this->licence(), json_encode(['details' => ['documentTemplate' => 12]]));
        $this->templateRepo->shouldReceive('fetchById')->with(12)->andThrow(new NotFoundException('gone'));
        $this->messageRepo->shouldNotReceive('save');

        $this->sut->handleCommand(Cmd::create(['document' => self::DOC_ID]));
    }

    public function testReadFailureIsLoggedAndSkipped(): void
    {
        $this->gate->shouldReceive('isEnabled')->andReturn(true);
        $this->document($this->licence());
        $this->messageRepo->shouldReceive('hasSmsForRecipientBetween')->andThrow(new \RuntimeException('db down'));
        $this->messageRepo->shouldNotReceive('save');

        $result = $this->sut->handleCommand(Cmd::create(['document' => self::DOC_ID]));

        $this->assertSame([], $result->getIds());
    }
}
