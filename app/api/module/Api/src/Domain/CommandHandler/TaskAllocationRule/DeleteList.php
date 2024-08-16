<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\TaskAllocationRule;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * DeleteList of Task Allocation Rules
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class DeleteList extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'TaskAllocationRule';

    protected $extraRepos = ['TaskAlphaSplit'];

    public function handleCommand(CommandInterface $command)
    {
        $rules = $this->getRepo()->fetchByIds($command->getIds());

        /* @var $taskAllocationRule \Dvsa\Olcs\Api\Entity\Task\TaskAllocationRule */
        foreach ($rules as $taskAllocationRule) {
            // delete all alpha splits attached to the rule first
            foreach ($taskAllocationRule->getTaskAlphaSplits() as $taskAlphaSplit) {
                $this->getRepo('TaskAlphaSplit')->delete($taskAlphaSplit);
            }
            $this->getRepo()->delete($taskAllocationRule);
            $this->result->addMessage("Task Allocation Rule ID {$taskAllocationRule->getId()} deleted");
        }

        return $this->result;
    }
}
