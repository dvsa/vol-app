<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\TaskAllocationRule;

use Doctrine\ORM\Exception\ORMException;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\TaskAllocationRule;

trait UpdateTaskAllocationRuleTrait
{
    /**
     * @param TaskAllocationRule\Create|TaskAllocationRule\Update $command
     * @return Entity\Task\TaskAllocationRule
     * @throws ORMException
     */
    private function updateTaskAllocationRule(
        Entity\Task\TaskAllocationRule $entity,
        Repository\TaskAllocationRule $repository,
        CommandInterface $command
    ): Entity\Task\TaskAllocationRule {
        $this->updateTaskAllocationRuleCommandInstanceCheck($command);

        $entity->setCategory(
            $repository->getReference(Entity\System\Category::class, $command->getCategory())
        );
        $entity->setSubCategory(
            $repository->getReference(Entity\System\SubCategory::class, $command->getSubCategory())
        );
        $entity->setTeam(
            $repository->getReference(Entity\User\Team::class, $command->getTeam())
        );
        $entity->setUser(
            $repository->getReference(Entity\User\User::class, $command->getUser())
        );
        $entity->setGoodsOrPsv($repository->getRefdataReference($command->getGoodsOrPsv()));
        $isMlh = null;
        if ($command->getIsMlh() === 'Y') {
            $isMlh = true;
        } elseif ($command->getIsMlh() === 'N') {
            $isMlh = false;
        }
        $entity->setIsMlh($isMlh);
        $entity->setTrafficArea(
            $repository->getReference(Entity\TrafficArea\TrafficArea::class, $command->getTrafficArea())
        );

        return $entity;
    }

    private function updateTaskAllocationRuleCommandInstanceCheck(CommandInterface $command): void
    {
        $expectedCommands = [
            TaskAllocationRule\Create::class,
            TaskAllocationRule\Update::class,
        ];
        if (!in_array($command::class, $expectedCommands)) {
            throw new \RuntimeException(sprintf(
                'Expected instance of: %s',
                implode('" or "', $expectedCommands)
            ));
        }
    }
}
