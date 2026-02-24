<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Queue\Service\Message;

use Dvsa\Olcs\Queue\Service\Message\AbstractMessage;
use PHPUnit\Framework\TestCase;

class AbstractMessageTest extends TestCase
{
    public function testAbstractMessage(): void
    {
        $messageBody = ['messageBody'];
        $queueUrl = 'someUrl';
        $delaySeconds = 10;
        $messageAttributes = [
            'attribute1' => 'value',
            'attribute2' => 'value',
            'attribute3' => 'value'
        ];

        $message = new class ($messageBody, $queueUrl) extends AbstractMessage
        {
        };

        $message->setDelaySeconds($delaySeconds);
        $message->setMessageAttributes($messageAttributes);

        $messageData = $message->toArray();
        $this->assertEquals($messageData['MessageBody'], $messageBody);
        $this->assertEquals($messageData['QueueUrl'], $queueUrl);
        $this->assertEquals($messageData['DelaySeconds'], $delaySeconds);
        $this->assertEquals($messageData['MessageAttributes'], $messageAttributes);
    }

    public function testAbstractMessageNoMessageData(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("messageData is empty");

        new class ([], 'someUrl') extends AbstractMessage
        {
        };
    }
}
