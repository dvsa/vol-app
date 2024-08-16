<?php

/**
 * Update Trailer
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Trailer;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Update Trailer
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
final class UpdateTrailer extends AbstractCommandHandler
{
    protected $repoServiceName = 'Trailer';

    public function handleCommand(CommandInterface $command)
    {
        $trailer = $this->getRepo()
            ->fetchById(
                $command->getId(),
                \Doctrine\ORM\Query::HYDRATE_OBJECT,
                $command->getVersion()
            );

        $trailer->setTrailerNo($command->getTrailerNo());
        $trailer->setIsLongerSemiTrailer($command->getIsLongerSemiTrailer() == 'Y');

        $this->getRepo()->save($trailer);

        $result = new Result();
        $result->addId('trailer', $trailer->getId());
        $result->addMessage('Trailer updated successfully');

        return $result;
    }
}
