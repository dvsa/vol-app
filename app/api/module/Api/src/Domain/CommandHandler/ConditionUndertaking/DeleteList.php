<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\ConditionUndertaking;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Delete a list of ConditionUndertaking
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class DeleteList extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'ConditionUndertaking';

    /**
     * Handle command
     *
     * @param \Dvsa\Olcs\Transfer\Command\ConditionUndertaking\DeleteList $command command
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var \Dvsa\Olcs\Api\Domain\Repository\ConditionUndertaking $repo */
        $repo = $this->getRepo();

        $ids = $command->getIds();

        foreach ($ids as $cuId) {
            /* @var $conditionUndertaking \Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking */
            $conditionUndertaking = $this->getRepo()->fetchById($cuId);
            $repo->delete($conditionUndertaking);

            $this->result->addMessage("ConditionUndertaking ID {$cuId} deleted");
        }

        //  clean in variations
        $cntDel = $repo->deleteFromVariations($ids);
        $this->result->addMessage('Deleted from variations ' . $cntDel  . ' conditionUndertaking');

        return $this->result;
    }
}
