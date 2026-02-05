<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Queue\Service;

use Aws\Command;
use Aws\Exception\AwsException;
use Aws\Result;
use Aws\Sqs\SqsClient;
use Dvsa\Olcs\Queue\Service\Queue;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Logging\Log\Logger;

class QueueTest extends MockeryTestCase
{
    protected $sut;

    protected $queue;

    /** @var  m\MockInterface|\Laminas\Log\Logger */
    protected $logger;

    public function setUp(): void
    {
        $this->queue = m::mock(SqsClient::class);
        $this->sut = new Queue($this->queue);

        parent::setUp();
    }

    public function testSendMessage(): void
    {
        $message = [
            'MessageBody' => 'some_message_body',
            'QueueUrl' => 'some_url'
        ];

        $this->queue->shouldReceive('sendMessage');

        $this->sut->sendMessage($message);

        $this->assertTrue(true);
    }

    public function testSendMessageWithExceptionThrown(): void
    {
        $this->logger = m::mock(\Laminas\Log\Logger::class);
        $this->logger->shouldReceive('err')
            ->with('Failed to send message. Error code: contents error. Error msg: message failed', [])
            ->once();

        Logger::setLogger($this->logger);

        $message = [
            'MessageAttributes' => [
                'messageType' => [
                    'DataType' => 'String',
                    'StringValue' => 'ch-org'
                ],
            ],
            'MessageBody' => 'some_message_body',
            'QueueUrl' => 'some_url'
        ];

        $context = [
            'message' => 'message failed',
            'code' => 'contents error'
        ];
        $exceptionCommand = new Command('name', []);

        $this->queue->shouldReceive('sendMessage')
            ->andThrow(new AwsException('failed to send message', $exceptionCommand, $context));

        $this->expectException(AwsException::class);

        $this->sut->sendMessage($message);
    }

    public function testFetchMessagesEmpty(): void
    {
        $this->queue->shouldReceive('receiveMessage')
            ->with([
                'MaxNumberOfMessages' => 3,
                'MessageAttributeNames' => ['All'],
                'QueueUrl' => 'queueUrl',
                'WaitTimeSeconds' => 1,
                'VisibilityTimeout' => 1
            ])
            ->andReturn(new Result(['Messages' => []]));

        $this->assertEmpty($this->sut->fetchMessages('queueUrl', 3));
    }

    public function testFetchMessagesNotEmpty(): void
    {
        $this->queue->shouldReceive('receiveMessage')
            ->with([
                'MaxNumberOfMessages' => 1,
                'MessageAttributeNames' => ['All'],
                'QueueUrl' => 'queueUrl',
                'WaitTimeSeconds' => 1,
                'VisibilityTimeout' => 1
            ])
            ->andReturn(new Result([
                'Messages' => [
                    [
                        'MessageId' => '1',
                        'ReceiptHandle' => 'abc1234',
                        'MD5OfBody' => '92ff23a0f07609a54f8e3b8f35616202',
                        'Body' => '1234',
                        'Attributes' => [],
                        'MD5OfMessageAttributes' => '92ff23a0i0760975hf8e3b8f35616202',
                        'MessageAttributes' => []
                    ]
                ]
            ]));

        $messages = $this->sut->fetchMessages('queueUrl', 1);
        $this->assertCount(1, $messages);
    }

    public function testFetchMessagesWithExceptionThrown(): void
    {
        $this->logger = m::mock(\Laminas\Log\Logger::class);
        $this->logger->shouldReceive('err')
            ->with('Failed to fetch message. Error code: contents error. Error msg: message failed', [])
            ->once();

        Logger::setLogger($this->logger);

        $context = [
            'message' => 'message failed',
            'code' => 'contents error'
        ];
        $exceptionCommand = new Command('name', []);

        $this->queue->shouldReceive('receiveMessage')
            ->andThrow(new AwsException('', $exceptionCommand, $context));

        $this->expectException(AwsException::class);

        $this->sut->fetchMessages('queueUrl', 1);
    }

    public function testFetchMessagesMoreThan10(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("maxMessages must be 10 or less");

        $this->sut->fetchMessages('queueUrl', 11);
    }

    public function testDeleteMessageWithExceptionThrown(): void
    {
        $this->logger = m::mock(\Laminas\Log\Logger::class);
        $this->logger->shouldReceive('err')
            ->with('Failed to delete message. Error code: contents error. Error msg: message failed', [])
            ->once();

        Logger::setLogger($this->logger);

        $context = [
            'message' => 'message failed',
            'code' => 'contents error'
        ];
        $exceptionCommand = new Command('name', []);

        $this->queue->shouldReceive('deleteMessage')->with([
            'QueueUrl' => 'queue_url',
            'ReceiptHandle' => 'receipt_handle'
        ])
            ->andThrow(new AwsException('', $exceptionCommand, $context));

        $this->expectException(AwsException::class);
        $this->sut->deleteMessage('queue_url', 'receipt_handle');
    }

    public function testDelete(): void
    {
        $this->queue->shouldReceive('deleteMessage')->with([
            'QueueUrl' => 'queue_url',
            'ReceiptHandle' => 'receipt_handle'
        ])->once()->andReturn(new Result([]));
        $actual = $this->sut->deleteMessage('queue_url', 'receipt_handle');
        $this->assertIsArray($actual);
    }
}
