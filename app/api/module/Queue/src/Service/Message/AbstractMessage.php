<?php

namespace Dvsa\Olcs\Queue\Service\Message;

/**
 * Class AbstractMessage
 *
 * The message object class for the message queue. All SQS message should extend this class.
 * For the message format see: https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/sqs-examples-send-receive-messages.html
 *
 * @package Dvsa\Olcs\Queue\Service\Message
 */
abstract class AbstractMessage implements MessageInterface
{
    /**
     * @var array
     */
    protected $message;

    /**
     * @var array
     */
    protected $messageData;

    public function __construct(array $messageData, string $queueUrl)
    {
        $this->messageData = $messageData;
        $this->message['QueueUrl'] = $queueUrl;
        $this->processMessageData();
    }

    public function setDelaySeconds(int $seconds): void
    {
        $this->message['DelaySeconds'] = $seconds;
    }

    public function setMessageAttributes(array $attributes): void
    {
        $this->message['MessageAttributes'] = $attributes;
    }

    public function toArray(): array
    {
        return $this->message;
    }

    public function processMessageData(): void
    {
        if (empty($this->messageData)) {
            throw new \InvalidArgumentException(
                "messageData is empty"
            );
        }
        $this->message['MessageBody'] = $this->messageData;
    }
}
