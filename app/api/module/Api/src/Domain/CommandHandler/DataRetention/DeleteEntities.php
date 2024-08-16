<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\DataRetention;

use Dvsa\Olcs\Api\Domain\Command\Queue\Create;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\BadRequestException;
use Dvsa\Olcs\Api\Domain\Repository\DataRetention;
use Dvsa\Olcs\Api\Domain\Repository\SystemParameter;
use Dvsa\Olcs\Api\Entity\Queue\Queue;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Olcs\Logging\Log\Logger;

/**
 * Class DeleteEntities
 */
final class DeleteEntities extends AbstractCommandHandler
{
    protected $repoServiceName = 'DataRetention';

    protected $extraRepos = ['Queue', 'SystemParameter'];

    /**
     * Handle command
     *
     * @param CommandInterface $command DTO
     *
     * @return Result
     * @throws BadRequestException
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var SystemParameter $systemParameterRepo */
        $systemParameterRepo = $this->getRepo('SystemParameter');
        if ($systemParameterRepo->getDisableDataRetentionDelete()) {
            throw new BadRequestException('Disabled by System Parameter');
        }

        // Get limit from arguments
        $limit = (int)$command->getLimit();
        // If not set in arguments then get from system paramter
        if ($limit === 0) {
            $limit = $systemParameterRepo->getDataRetentionDeleteLimit();
        }
        $systemUserId = $systemParameterRepo->getSystemDataRetentionUser();

        /** @var DataRetention $repo */
        $repo = $this->getRepo();

        try {
            $repo->runCleanupProc($limit, $systemUserId);

            // Create queue job to remove deleted documents, if not already exists
            if (!$this->getRepo('Queue')->isItemTypeQueued(Queue::TYPE_REMOVE_DELETED_DOCUMENTS)) {
                $command = Create::create(
                    ['type' => Queue::TYPE_REMOVE_DELETED_DOCUMENTS, 'status' => Queue::STATUS_QUEUED]
                );
                $this->handleSideEffect($command);
            }
        } catch (\Exception $e) {
            Logger::err(
                sprintf(
                    'Error on DeleteEntities: %s',
                    $e->getMessage()
                )
            );
            throw $e;
        }
        return $this->result;
    }
}
