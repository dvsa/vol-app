<?php

namespace Dvsa\Olcs\Cli\Service\Queue\Consumer;

use Dvsa\Olcs\Api\Domain\Command\Queue\Complete as CompleteCmd;
use Dvsa\Olcs\Api\Domain\Command\Queue\Failed as FailedCmd;
use Dvsa\Olcs\Api\Domain\Command\Queue\Retry as RetryCmd;
use Dvsa\Olcs\Api\Domain\CommandHandlerManager;
use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Olcs\Logging\Log\Logger;

/**
 * Abstract Queue Consumer
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
abstract class AbstractConsumer implements MessageConsumerInterface
{
    /** @var CommandHandlerManager */
    protected $commandHandlerManager;

    /**
     * Create service instance
     *
     *
     * @return AbstractConsumer
     */
    public function __construct(AbstractConsumerServices $abstractConsumerServices)
    {
        $this->commandHandlerManager = $abstractConsumerServices->getCommandHandlerManager();
    }

    /**
     * Called when processing the message was successful
     *
     * @param QueueEntity $item    queue item
     * @param string|null $message success message
     *
     * @return string
     */
    protected function success(QueueEntity $item, $message = null)
    {
        $command = CompleteCmd::create(['item' => $item]);
        $this->handleSideEffectCommand($command);

        $description = 'Successfully processed message';
        $content = $item->getId() . ' ' . $item->getOptions() . ($message ? ' ' . $message : '');

        Logger::log(
            \Laminas\Log\Logger::DEBUG,
            $description,
            ['errorLevel' => 0, 'content' => $content]
        );

        return $description . ': ' . $content;
    }

    /**
     * Mark the message as failed
     *
     * @param QueueEntity $item   queue item
     * @param string      $reason exception message passed from the command handler
     *
     * @return string
     */
    public function failed(QueueEntity $item, $reason = null)
    {
        $command = FailedCmd::create(
            [
                'item' => $item,
                'lastError' => $reason,
            ]
        );
        $this->handleSideEffectCommand($command);

        $description = 'Failed to process message';
        $content = $item->getId() . ' ' . $item->getOptions() . ' ' .  $reason;

        Logger::log(
            \Laminas\Log\Logger::ERR,
            $description,
            ['errorLevel' => 1, 'content' => $content]
        );

        return $description . ': ' . $content;
    }

    /**
     * Requeue the message
     *
     * @param QueueEntity $item       queue item
     * @param string      $retryAfter (seconds)
     * @param string|null $reason     exception message passed from the command handler
     *
     * @return string
     */
    protected function retry(QueueEntity $item, $retryAfter, $reason = null)
    {
        $command = RetryCmd::create(['item' => $item, 'retryAfter' => $retryAfter, 'lastError' => $reason]);
        $this->handleSideEffectCommand($command);

        $description = 'Requeued message';
        $content = $item->getId() . ' ' . $item->getOptions() . ' for retry in ' .  $retryAfter . ' ' .  $reason;

        Logger::log(
            \Laminas\Log\Logger::WARN,
            $description,
            ['errorLevel' => 0, 'content' => $content]
        );

        return $description . ': ' . $content;
    }

    /**
     * Run a DTO command side effect
     *
     * @param CommandInterface $command the command
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     */
    protected function handleSideEffectCommand(CommandInterface $command)
    {
        return $this->commandHandlerManager->handleCommand($command, false);
    }

    /**
     * Run a DTO command
     *
     * @param CommandInterface $command the command
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     */
    protected function handleCommand(CommandInterface $command)
    {
        return $this->commandHandlerManager->handleCommand($command);
    }
}
