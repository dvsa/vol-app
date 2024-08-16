<?php

/**
 * Create Trailer
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Trailer;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Licence\Trailer;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Create Trailer
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
final class CreateTrailer extends AbstractCommandHandler
{
    protected $repoServiceName = 'Trailer';

    public function handleCommand(CommandInterface $command)
    {
        $licence = $this->getRepo()
            ->getReference(Licence::class, $command->getLicence());

        $trailer = new Trailer();
        $trailer->setTrailerNo($command->getTrailerNo());
        $trailer->setIsLongerSemiTrailer($command->getIsLongerSemiTrailer() == 'Y');
        $trailer->setSpecifiedDate(new \DateTime($command->getSpecifiedDate()));
        $trailer->setLicence($licence);

        $this->getRepo()->save($trailer);

        $result = new Result();
        $result->addId('trailer', $trailer->getId());
        $result->addMessage('Trailer created successfully');

        return $result;
    }
}
