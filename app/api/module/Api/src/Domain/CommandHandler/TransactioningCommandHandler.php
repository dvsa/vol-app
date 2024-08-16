<?php

/**
 * Class TransactioningCommandHandler
 * @package Dvsa\Olcs\Api\Domain\CommandHandler
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler;

use Dvsa\Olcs\Api\Domain\Repository\TransactionManagerInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Class TransactioningCommandHandler
 * @package Dvsa\Olcs\Api\Domain\CommandHandler
 */
class TransactioningCommandHandler implements CommandHandlerInterface
{
    public function __construct(private readonly CommandHandlerInterface $wrapped, private readonly TransactionManagerInterface $repo)
    {
    }

    public function getWrapped()
    {
        return $this->wrapped;
    }

    /**
     * @param CommandInterface $command
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     * @throws \Exception
     */
    public function handleCommand(CommandInterface $command)
    {
        try {
            $this->repo->beginTransaction();
            $result = $this->wrapped->handleCommand($command);
            $this->repo->commit();
            return $result;
        } catch (\Exception $e) {
            if (method_exists($this->wrapped, 'rollbackCommand')) {
                // wrapped command rollback
                $this->wrapped->rollbackCommand($command, $e);
            }
            $this->repo->rollback();
            throw $e;
        }
    }

    /**
     * @return bool
     * @throws \Dvsa\Olcs\Api\Domain\Exception\DisabledHandlerException
     */
    public function checkEnabled(): bool
    {
        return $this->wrapped->checkEnabled();
    }
}
