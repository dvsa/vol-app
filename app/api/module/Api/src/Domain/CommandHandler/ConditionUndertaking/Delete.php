<?php

/**
 * Delete ConditionUndertaking
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\ConditionUndertaking;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractDeleteCommandHandler;
use Dvsa\Olcs\Api\Entity as Entities;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking;
use Dvsa\Olcs\Api\Entity\EventHistory\EventHistoryType as EventHistoryTypeEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\EventHistory\Creator as EventHistoryCreator;
use Psr\Container\ContainerInterface;

/**
 * Delete ConditionUndertaking
 */
final class Delete extends AbstractDeleteCommandHandler
{
    protected $repoServiceName = 'ConditionUndertaking';

    /**
     * Handle command
     *
     * @param CommandInterface $command command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        
        /** @var \Dvsa\Olcs\Api\Domain\Repository\ConditionUndertaking $repo */
        $repo = $this->getRepo();

        /* @var $conditionUndertaking ConditionUndertaking */
        $conditionUndertaking = $this->getRepo()->fetchById(
            $command->getId()
        );

        $result = parent::handleCommand($command);

        $value = $result->getIds()['id' . $command->getId()] ?? null;

        if ($value == $command->getid() && $conditionUndertaking->getConditionType() instanceof RefData) {
            $eventHistoryType = $conditionUndertaking->getConditionType()->getId() === ConditionUndertaking::TYPE_CONDITION ? 
                EventHistoryTypeEntity::EVENT_CODE_CONDITION_DELETED : EventHistoryTypeEntity::EVENT_CODE_UNDERTAKING_DELETED;
            
            // create Event History record
            $this->eventHistoryCreator->create($conditionUndertaking, $eventHistoryType);
        }

        return $result;
    }

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $fullContainer = $container;

        $this->eventHistoryCreator = $container->get('EventHistoryCreator');
        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}
