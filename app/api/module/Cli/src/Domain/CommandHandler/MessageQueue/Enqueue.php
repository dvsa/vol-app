<?php

namespace Dvsa\Olcs\Cli\Domain\CommandHandler\MessageQueue;

use Aws\Exception\AwsException;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Queue\Service\Message\MessageInterface;
use Dvsa\Olcs\Queue\Service\QueueInterface;
use Dvsa\Olcs\Queue\Service\QueueServiceTrait;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Olcs\Logging\Log\Logger;

class Enqueue extends AbstractCommandHandler implements QueueInterface
{
    use QueueServiceTrait;

    /**
     * @var int
     */
    private $successfulMessageCount = 0;

    /**
     * @var int
     */
    private $failedMessageCount = 0;

    /**
     * @param CommandInterface $command
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /**
         * @var $command \Dvsa\Olcs\Api\Domain\Command\MessageQueue\Enqueue
         */

        $messages = $this->messageBuilderService->buildMessages(
            $command->getMessageData(),
            $command->getMessageType(),
            $this->queueConfig
        );

        foreach ($messages as $message) {
            try {
                $this->queueService->sendMessage($message->toArray());
                $this->successfulMessageCount++;
            } catch (AwsException) {
                $this->failedMessageCount++;
            } catch (\Exception $exception) {
                Logger::notice(
                    'Failed to send message to SQS queue: ' . $exception::class . '::' . $exception->getMessage(),
                    ['data' => $message->toArray()]
                );
                $this->failedMessageCount++;
            }
        }

        $this->result->addMessage(
            $this->successfulMessageCount . ' messages of type ' . $command->getMessageType() . ' successfully added to the queue.'
        );

        $this->result->addMessage(
            $this->failedMessageCount . ' messages of type ' . $command->getMessageType() . ' could not be added to the queue.'
        );
        return $this->result;
    }
}
