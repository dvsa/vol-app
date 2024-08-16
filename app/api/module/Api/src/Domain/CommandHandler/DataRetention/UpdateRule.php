<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\DataRetention;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Repository\DataRetentionRule;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\DataRetentionRule as DataRetentionRuleEntity;
use Dvsa\Olcs\Transfer\Command\DataRetention\UpdateRule as UpdateRuleCommand;

/**
 * Class UpdateRule
 */
final class UpdateRule extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'DataRetentionRule';

    /**
     * Handle command
     *
     * @param CommandInterface|UpdateRuleCommand $command DTO
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {

        /** @var DataRetentionRule $repo */
        $repo = $this->getRepo();

        /** @var DataRetentionRuleEntity $dataRetentionRule */
        $dataRetentionRule = $repo->fetchById($command->getId());
        $dataRetentionRule->setDescription($command->getDescription());
        $dataRetentionRule->setRetentionPeriod($command->getRetentionPeriod());
        $dataRetentionRule->setMaxDataSet($command->getMaxDataSet());
        $dataRetentionRule->setIsEnabled($command->getIsEnabled());
        $dataRetentionRule->setActionType($repo->getRefdataReference($command->getActionType()));

        // Update entity
        $repo->save($dataRetentionRule);

        $result = new Result();
        $result->addId('data-retention-rule', $dataRetentionRule->getId());
        $result->addMessage('Rule updated successfully');

        return $result;
    }
}
