<?php

/**
 * Delete Previous Conviction
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\PreviousConviction;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Delete Previous Conviction
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
final class DeletePreviousConviction extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'PreviousConviction';

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        foreach ($command->getIds() as $id) {
            $this->getRepo()->delete(
                $this->getRepo()->fetchById($id)
            );

            $result->addId('previousConviction' . $id, $id);
            $result->addMessage('Previous conviction removed');
        }

        return $result;
    }
}
