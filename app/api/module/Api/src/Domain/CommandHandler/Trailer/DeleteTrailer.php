<?php

/**
 * Delete Trailer
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Trailer;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Delete Trailer
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
final class DeleteTrailer extends AbstractCommandHandler
{
    protected $repoServiceName = 'Trailer';

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        foreach ($command->getIds() as $trailer) {
            $this->getRepo()->delete(
                $this->getRepo()->fetchById($trailer)
            );

            $result->addId('trailer' . $trailer, $trailer);
            $result->addMessage('Trailer removed');
        }

        return $result;
    }
}
