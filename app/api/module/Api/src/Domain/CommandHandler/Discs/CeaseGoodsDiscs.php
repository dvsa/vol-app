<?php

/**
 * Cease Goods Discs
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Discs;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Discs\CeaseGoodsDiscs as Cmd;

/**
 * Cease Goods Discs for a Licence
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class CeaseGoodsDiscs extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'GoodsDisc';

    /**
     * Handle command
     *
     * @param CommandInterface $command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var Cmd $command */
        $this->getRepo()->ceaseDiscsForLicence($command->getLicence());

        $result = new Result();
        $result->addMessage('Ceased discs for licence.');

        return $result;
    }
}
