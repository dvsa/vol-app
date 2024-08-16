<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Bus\Ebsr;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\QueueAwareTrait;
use Dvsa\Olcs\Api\Entity\Queue\Queue;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Bus\Ebsr\ProcessPack as ProcessPackCmd;
use Dvsa\Olcs\Api\Domain\Command\Bus\Ebsr\ProcessPackTransaction as ProcessPackTransactionCmd;
use Dvsa\Olcs\Api\Domain\Command\Queue\Create as CreateQueueCmd;

/**
 * Process Ebsr pack
 */
final class ProcessPack extends AbstractCommandHandler
{
    use QueueAwareTrait;

    /**
     * Process the EBSR pack
     * Error information is added into the ebsr_submission_result column of the ebsr_submission table
     *
     * @param CommandInterface|ProcessPackCmd $command command to process EBSR pack
     *
     * @return Result
     * @throws \Exception
     */
    public function handleCommand(CommandInterface $command)
    {
        try {
            $data = $command->getArrayCopy();
            $command = ProcessPackTransactionCmd::create($data);
            $this->result->merge($this->handleSideEffect($command));
            return $this->result;
        } catch (\Exception $e) {
            $command = $this->createQueueCmd($data['id'], $e->getMessage());
            $this->handleSideEffect($command);
            throw new ProcessPackException($e->getMessage());
        }
    }

    /**
     * Adds the EBSR submission failure to the queue
     *
     * @param int $ebsrId
     * @param int $organisationId
     *
     * @return CreateQueueCmd
     */
    private function createQueueCmd($ebsrId, $message = null)
    {
        $options = [
            'id' => $ebsrId,
            'message' => $message
        ];

        $processAfterDate = (new \DateTime())->add(new \DateInterval('PT5M'))->format('Y-m-d H:i:s');

        return $this->createQueue($ebsrId, Queue::TYPE_EBSR_PACK_FAILED, $options, $processAfterDate);
    }
}
