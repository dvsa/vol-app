<?php

namespace Dvsa\Olcs\Queue\Service\Message;

class MessageBuilder
{
    /**
     * Builds an array of messages based on the message type
     *
     *
     * @return array
     */
    public function buildMessages(array $messageData, string $messageType, array $queueConfig): array
    {
        $this->validateMessageType($messageType);
        $this->validateQueueUrl($queueUrl = $this->getQueueUrl($messageType), $queueConfig);

        $messages = [];
        foreach ($messageData as $messageDatum) {
            $messages[] = new $messageType($messageDatum, $queueConfig[$queueUrl]);
        }
        return $messages;
    }

    public function buildMessage(array $messageData, string $messageType, array $queueConfig): MessageInterface
    {
        $this->validateMessageType($messageType);
        $this->validateQueueUrl($queueUrl = $this->getQueueUrl($messageType), $queueConfig);

        return new $messageType($messageData, $queueConfig[$queueUrl]);
    }

    private function getQueueUrl($messageType): string
    {
        $path = explode('\\', (string) $messageType);
        $path = array_pop($path);

        return $path . '_URL';
    }

    private function validateQueueUrl($queueUrl, $queueConfig): void
    {
        if (!array_key_exists($queueUrl, $queueConfig)) {
            throw new \InvalidArgumentException(
                "The url config $queueUrl does not exist"
            );
        }
    }

    private function validateMessageType($messageType): void
    {
        if (!class_exists($messageType)) {
            throw new \InvalidArgumentException(
                "The $messageType class does not exist"
            );
        }
    }
}
