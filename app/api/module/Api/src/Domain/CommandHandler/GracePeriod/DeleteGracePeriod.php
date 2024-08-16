<?php

/**
 * Delete Grace Period
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\GracePeriod;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Delete Grace Period
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
final class DeleteGracePeriod extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'GracePeriod';

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        foreach ($command->getIds() as $gracePeriod) {
            $this->getRepo()->delete(
                $this->getRepo()->fetchById($gracePeriod)
            );

            $result->addId('graceperiod' . $gracePeriod, $gracePeriod);
            $result->addMessage('Grace period removed');
        }

        return $result;
    }
}
