<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\DataRetention;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Repository\DataRetention;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\DataRetention\DataRetention as DataRetentionEntity;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\Olcs\Transfer\Command\DataRetention\AssignItems as AssignItemsCommand;

/**
 * Class AssignItems
 */
final class AssignItems extends AbstractCommandHandler implements TransactionedInterface
{
    public const string ERR_USER = 'can\'t assign data retention record to this user';

    protected $repoServiceName = 'DataRetention';

    /**
     * Handle command
     *
     * @param CommandInterface|AssignItemsCommand $command DTO
     *
     * @return Result
     * @throws \RuntimeException
     */
    public function handleCommand(CommandInterface $command)
    {
        /**
         * @var DataRetention $repo
         * @var UserEntity    $userRecord
         */
        $repo = $this->getRepo();
        $userRecord = $repo->getReference(UserEntity::class, $command->getUser());

        if (!$userRecord->canBeAssignedDataRetention()) {
            throw new \RuntimeException(self::ERR_USER);
        }

        foreach ($command->getIds() as $id) {
            /** @var DataRetentionEntity $dataRetentionRecord */
            $dataRetentionRecord = $repo->fetchById($id);
            $dataRetentionRecord->setAssignedTo($userRecord);

            $this->getRepo()->save($dataRetentionRecord);
        }

        $this->result->addMessage(count($command->getIds()) . ' Data retention record(s) updated');

        return $this->result;
    }
}
