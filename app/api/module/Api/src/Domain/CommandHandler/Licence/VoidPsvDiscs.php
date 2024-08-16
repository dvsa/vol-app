<?php

/**
 * Void Psv Discs
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Licence;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Licence\PsvDisc;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Void Psv Discs
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class VoidPsvDiscs extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'PsvDisc';

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        $ids = $command->getIds();

        foreach ($ids as $id) {
            /** @var PsvDisc $psvDisc */
            $psvDisc = $this->getRepo()->fetchById($id);
            $psvDisc->cease();
            $this->getRepo()->save($psvDisc);
        }

        $result->addMessage(count($ids) . ' PSV Disc(s) voided');

        return $result;
    }
}
