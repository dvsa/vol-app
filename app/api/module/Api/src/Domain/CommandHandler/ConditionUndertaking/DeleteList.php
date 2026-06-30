<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\ConditionUndertaking;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\EventHistory\EventHistoryType as EventHistoryTypeEntity;
use Dvsa\Olcs\Api\Service\EventHistory\Creator as EventHistoryCreator;
use Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking;
use Psr\Container\ContainerInterface;

/**
 * Delete a list of ConditionUndertaking
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class DeleteList extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'ConditionUndertaking';

    private EventHistoryCreator $eventHistoryCreator;

    /**
     * Handle command
     *
     * @param \Dvsa\Olcs\Transfer\Command\ConditionUndertaking\DeleteList $command command
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     */
    #[\Override]
    public function handleCommand(CommandInterface $command)
    {
        /** @var \Dvsa\Olcs\Api\Domain\Repository\ConditionUndertaking $repo */
        $repo = $this->getRepo();

        $ids = $command->getIds();

        foreach ($ids as $cuId) {
            /* @var $conditionUndertaking \Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking */
            $conditionUndertaking = $this->getRepo()->fetchById($cuId);
            $repo->delete($conditionUndertaking);

            $eventHistoryType = $conditionUndertaking->getConditionType()->getId() === ConditionUndertaking::TYPE_CONDITION ?
                EventHistoryTypeEntity::EVENT_CODE_CONDITION_DELETED : EventHistoryTypeEntity::EVENT_CODE_UNDERTAKING_DELETED;

            // create Event History record
            $this->eventHistoryCreator->create($conditionUndertaking, $eventHistoryType);

            $this->result->addMessage("ConditionUndertaking ID {$cuId} deleted");
        }

        //  clean in variations
        $cntDel = $repo->deleteFromVariations($ids);
        $this->result->addMessage('Deleted from variations ' . $cntDel  . ' conditionUndertaking');

        return $this->result;
    }

    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $this->eventHistoryCreator = $container->get('EventHistoryCreator');
        return parent::__invoke($container, $requestedName, $options);
    }
}
