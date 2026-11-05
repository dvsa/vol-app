<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Service\Queue\Consumer\Sms;

use Dvsa\Olcs\Api\Domain\Command\Queue\Complete;
use Dvsa\Olcs\Api\Domain\Command\Queue\Retry;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Sms\SendSms;
use Dvsa\Olcs\Api\Domain\Exception\NotReadyException;
use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Cli\Service\Queue\Consumer\Sms\Send;
use Dvsa\OlcsTest\Cli\Service\Queue\Consumer\AbstractConsumerTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(Send::class)]
final class SendTest extends AbstractConsumerTestCase
{
    protected $consumerClass = Send::class;

    private function item(): QueueEntity
    {
        $item = new QueueEntity();
        $item->setId(99);
        $item->setOptions(json_encode(['commandClass' => SendSms::class, 'commandData' => ['id' => 5]]));

        return $item;
    }

    public function testSuccessCompletes(): void
    {
        $item = $this->item();
        $this->expectCommand(SendSms::class, ['id' => 5], (new Result())->addMessage('ok'));
        $this->expectCommand(Complete::class, ['item' => $item], new Result(), false);

        $this->assertStringStartsWith('Successfully processed message: 99', $this->sut->processMessage($item));
    }

    public function testNotReadyRetries(): void
    {
        $item = $this->item();
        $e = (new NotReadyException('later'))->setRetryAfter(900);
        $this->chm->shouldReceive('handleCommand')->with(SendSms::class)->andThrow($e);
        $this->expectCommand(
            Retry::class,
            ['item' => $item, 'retryAfter' => 900, 'lastError' => 'later'],
            new Result(),
            false
        );

        $this->assertStringStartsWith('Requeued message: 99', $this->sut->processMessage($item));
    }

    public function testMaxAttemptsIsFour(): void
    {
        $this->assertSame(4, (new \ReflectionProperty(Send::class, 'maxAttempts'))->getDefaultValue());
    }
}
