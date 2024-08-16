<?php

namespace Dvsa\Olcs\Cli\Domain\CommandHandler\Bus;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Cli\Domain\Command\Bus\Expire as ExpireCmd;
use Dvsa\Olcs\Api\Domain\Repository\Bus as BusRepo;
use Dvsa\Olcs\Api\Domain\Command\Result;

/**
 * Expire bus registrations that have reached their end date
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
final class Expire extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Bus';

    /**
     * Handle command
     *
     * @param CommandInterface|ExpireCmd $command command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        /** @var BusRepo $repo */
        $repo = $this->getRepo();
        $rowCount = $repo->expireRegistrations();
        $result->addMessage($rowCount . ' registrations have been expired');

        return $result;
    }
}
