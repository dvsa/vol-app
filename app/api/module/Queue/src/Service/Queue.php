<?php

namespace Dvsa\Olcs\Queue\Service;

use Aws\Exception\AwsException;
use Aws\Sqs\SqsClient;
use Olcs\Logging\Log\Logger;

class Queue
{
    /**
     * @var SqsClient
     */
    protected $sqsClient;

    public const SEND_ACTION = 'send';
    public const FETCH_ACTION = 'fetch';
    public const DELETE_ACTION = 'delete';
    public const SET_VISIBILITY_TIMEOUT_ACTION = 'set visibility timeout';

    public function __construct(SqsClient $sqsClient)
    {
        $this->sqsClient = $sqsClient;
    }

    /**
     * Sends message to sqs queue and returns the response code
     *
     *
     */
    public function sendMessage(array $message): void
    {
        try {
            $this->sqsClient->sendMessage($message);
        } catch (AwsException $exception) {
            $this->logAwsException($exception, static::SEND_ACTION);
            throw $exception;
        }
    }

    /**
     *
     * @param int $maxMessages Number of messages to fetch. No more than 10
     *
     * @return array|null
     */
    public function fetchMessages(string $queueUrl, int $maxMessages, int $visibilityTimeout = 1): ?array
    {
        if ($maxMessages > 10) {
            throw new \InvalidArgumentException('maxMessages must be 10 or less');
        }

        try {
            $result = $this->sqsClient->receiveMessage([
                'MaxNumberOfMessages' => $maxMessages,
                'MessageAttributeNames' => ['All'],
                'QueueUrl' => $queueUrl,
                'WaitTimeSeconds' => 1,
                'VisibilityTimeout' => $visibilityTimeout
            ]);

            return $result->get('Messages');
        } catch (AwsException $exception) {
            $this->logAwsException($exception, static::FETCH_ACTION);
            throw $exception;
        }
    }

    /**
     * @return array
     */
    public function deleteMessage(string $queueUrl, string $receiptHandle): array
    {
        try {
            return $this->sqsClient->deleteMessage([
                'QueueUrl' => $queueUrl,
                'ReceiptHandle' => $receiptHandle
            ])->toArray();
        } catch (AwsException $exception) {
            $this->logAwsException($exception, static::DELETE_ACTION);
            throw $exception;
        }
    }

    public function changeMessageVisibility(string $queueUrl, string $receiptHandle, int $visibilityTimeout): array
    {
        try {
            return $this->sqsClient->changeMessageVisibility([
                'QueueUrl' => $queueUrl,
                'ReceiptHandle' => $receiptHandle,
                'VisibilityTimeout' => $visibilityTimeout,
            ])->toArray();
        } catch (AwsException $exception) {
            $this->logAwsException($exception, static::SET_VISIBILITY_TIMEOUT_ACTION);
            throw $exception;
        }
    }

    /**
     * @return void
     */
    private function logAwsException(AwsException $exception, string $action): void
    {
        Logger::err(
            'Failed to ' . $action . ' message. Error code: ' .
            $exception->getAwsErrorCode() . ". Error msg: " .
            $exception->getAwsErrorMessage()
        );
    }
}
