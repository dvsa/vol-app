<?php

/**
 * CeasePsvDiscs.php
 *
 * @author Joshua Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Discs;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;

/**
 * Class CeasePsvDiscs
 *
 * 'Cease' the goods discs on a licence.
 *
 * @package Dvsa\Olcs\Api\Domain\CommandHandler\Licence
 */
final class CeasePsvDiscs extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'PsvDisc';

    public function handleCommand(CommandInterface $command)
    {
        $this->getRepo()->ceaseDiscsForLicence($command->getLicence());

        $result = new Result();
        $result->addMessage('Discs ceased');

        return $result;
    }
}
