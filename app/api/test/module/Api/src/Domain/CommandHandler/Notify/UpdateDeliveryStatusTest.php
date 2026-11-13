<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Notify;

use Dvsa\Olcs\Api\Domain\CommandHandler\Notify\UpdateDeliveryStatus;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Entity\Notify\NotifyMessage;
use Dvsa\Olcs\Api\Service\Sms\SmsConfig;
use Dvsa\Olcs\Transfer\Command\Notify\UpdateDeliveryStatus as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(UpdateDeliveryStatus::class)]
final class UpdateDeliveryStatusTest extends AbstractCommandHandlerTestCase
{
    private const string NOTIFY_ID = '740e5834-3a29-46b4-9a6f-16142fde533a';

    private $messageRepo;

    public function setUp(): void
    {
        $this->sut = new UpdateDeliveryStatus();
        $this->mockedSmServices[SmsConfig::class] = new SmsConfig('key', false, null, 1025, 'tmpl-uuid', 'dev');
        $this->messageRepo = $this->mockRepo('NotifyMessage', Repository\NotifyMessage::class);

        $this->refData = [
            NotifyMessage::STATUS_SENT,
            NotifyMessage::STATUS_DELIVERED,
            NotifyMessage::STATUS_PERMANENT_FAILURE,
            NotifyMessage::STATUS_TEMPORARY_FAILURE,
            NotifyMessage::STATUS_TECHNICAL_FAILURE,
        ];

        parent::setUp();
    }

    private function message(): NotifyMessage
    {
        $status = $this->refData[NotifyMessage::STATUS_SENT];
        $message = new NotifyMessage($status, $status, '+447700900123', new \DateTime());
        $message->setId(555);
        $message->setNotifyTemplateId('tmpl-uuid');

        return $message;
    }

    public function testDeliveredIsRecorded(): void
    {
        $message = $this->message();
        $this->messageRepo->shouldReceive('fetchByNotifyId')->once()->with(self::NOTIFY_ID)->andReturn($message);
        $this->messageRepo->shouldReceive('save')->once()->with($message);

        $this->sut->handleCommand(Cmd::create([
            'id' => self::NOTIFY_ID,
            'status' => 'delivered',
            'completedAt' => '2026-11-11T12:00:00.000000Z',
        ]));

        $this->assertSame(NotifyMessage::STATUS_DELIVERED, $message->getStatus()->getId());
        $this->assertSame('2026-11-11 12:00:00', $message->getCompletedOn()->format('Y-m-d H:i:s'));
        $this->assertNotNull($message->getStatusUpdatedOn());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('statusProvider')]
    public function testStatusMapping(string $notifyStatus, string $expected): void
    {
        $message = $this->message();
        $this->messageRepo->shouldReceive('fetchByNotifyId')->once()->with(self::NOTIFY_ID)->andReturn($message);
        $this->messageRepo->shouldReceive('save')->once()->with($message);

        $this->sut->handleCommand(Cmd::create(['id' => self::NOTIFY_ID, 'status' => $notifyStatus]));

        $this->assertSame($expected, $message->getStatus()->getId());
    }

    public static function statusProvider(): array
    {
        return [
            ['sent', NotifyMessage::STATUS_SENT],
            ['permanent-failure', NotifyMessage::STATUS_PERMANENT_FAILURE],
            ['temporary-failure', NotifyMessage::STATUS_TEMPORARY_FAILURE],
            ['technical-failure', NotifyMessage::STATUS_TECHNICAL_FAILURE],
        ];
    }

    public function testUnknownIdIsIgnored(): void
    {
        $this->messageRepo->shouldReceive('fetchByNotifyId')->once()->with(self::NOTIFY_ID)->andReturn(null);
        $this->messageRepo->shouldReceive('save')->never();

        $this->sut->handleCommand(Cmd::create(['id' => self::NOTIFY_ID, 'status' => 'delivered']));
    }

    public function testUnknownStatusIsIgnored(): void
    {
        $this->messageRepo->shouldReceive('fetchByNotifyId')->never();
        $this->messageRepo->shouldReceive('save')->never();

        $this->sut->handleCommand(Cmd::create(['id' => self::NOTIFY_ID, 'status' => 'created']));
    }

    public function testReferenceFindsRowWithNoNotifyIdYet(): void
    {
        $message = $this->message();
        $message->setErrorCode(0);
        $message->setErrorMessage('timed out');
        $this->messageRepo->shouldReceive('fetchByNotifyId')->once()->with(self::NOTIFY_ID)->andReturn(null);
        $this->messageRepo->shouldReceive('fetchById')->once()->with(555)->andReturn($message);
        $this->messageRepo->shouldReceive('save')->once()->with($message);

        $this->sut->handleCommand(Cmd::create([
            'id' => self::NOTIFY_ID,
            'status' => 'delivered',
            'reference' => 'dev-sms-555',
            'sentAt' => '2026-11-11T11:59:00.000000Z',
        ]));

        $this->assertSame(NotifyMessage::STATUS_DELIVERED, $message->getStatus()->getId());
        $this->assertSame(self::NOTIFY_ID, $message->getNotifyId());
        $this->assertSame('2026-11-11 11:59:00', $message->getSentOn()->format('Y-m-d H:i:s'));
        $this->assertNull($message->getErrorCode());
        $this->assertNull($message->getErrorMessage());
    }

    public function testReferenceMatchSetsSentOnToNowWhenNotifyGivesNoSentAt(): void
    {
        $message = $this->message();
        $this->messageRepo->shouldReceive('fetchByNotifyId')->once()->with(self::NOTIFY_ID)->andReturn(null);
        $this->messageRepo->shouldReceive('fetchById')->once()->with(555)->andReturn($message);
        $this->messageRepo->shouldReceive('save')->once()->with($message);

        $this->sut->handleCommand(Cmd::create([
            'id' => self::NOTIFY_ID,
            'status' => 'delivered',
            'reference' => 'dev-sms-555',
        ]));

        $this->assertNotNull($message->getSentOn());
    }

    public function testReferenceFromAnotherEnvironmentIsIgnored(): void
    {
        $this->messageRepo->shouldReceive('fetchByNotifyId')->once()->with(self::NOTIFY_ID)->andReturn(null);
        $this->messageRepo->shouldReceive('fetchById')->never();
        $this->messageRepo->shouldReceive('save')->never();

        $this->sut->handleCommand(Cmd::create([
            'id' => self::NOTIFY_ID,
            'status' => 'delivered',
            'reference' => 'int-sms-555',
        ]));
    }

    public function testReferenceIgnoredWhenRowAlreadyHasANotifyId(): void
    {
        $message = $this->message();
        $message->setNotifyId('11111111-1111-1111-1111-111111111111');
        $this->messageRepo->shouldReceive('fetchByNotifyId')->once()->with(self::NOTIFY_ID)->andReturn(null);
        $this->messageRepo->shouldReceive('fetchById')->once()->with(555)->andReturn($message);
        $this->messageRepo->shouldReceive('save')->never();

        $this->sut->handleCommand(Cmd::create([
            'id' => self::NOTIFY_ID,
            'status' => 'delivered',
            'reference' => 'dev-sms-555',
        ]));

        $this->assertSame(NotifyMessage::STATUS_SENT, $message->getStatus()->getId());
    }

    public function testReferenceForMissingRowIsIgnored(): void
    {
        $this->messageRepo->shouldReceive('fetchByNotifyId')->once()->with(self::NOTIFY_ID)->andReturn(null);
        $this->messageRepo->shouldReceive('fetchById')->once()->with(555)->andThrow(new NotFoundException('gone'));
        $this->messageRepo->shouldReceive('save')->never();

        $this->sut->handleCommand(Cmd::create([
            'id' => self::NOTIFY_ID,
            'status' => 'delivered',
            'reference' => 'dev-sms-555',
        ]));
    }

    public function testReferenceForRowNeverSentToNotifyIsIgnored(): void
    {
        $message = $this->message();
        $message->setNotifyTemplateId(null);
        $this->messageRepo->shouldReceive('fetchByNotifyId')->once()->with(self::NOTIFY_ID)->andReturn(null);
        $this->messageRepo->shouldReceive('fetchById')->once()->with(555)->andReturn($message);
        $this->messageRepo->shouldReceive('save')->never();

        $this->sut->handleCommand(Cmd::create([
            'id' => self::NOTIFY_ID,
            'status' => 'delivered',
            'reference' => 'dev-sms-555',
        ]));

        $this->assertSame(NotifyMessage::STATUS_SENT, $message->getStatus()->getId());
    }
}
