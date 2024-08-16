<?php

/**
 * Update Trailers
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Licence;

use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;

/**
 * Update Trailers
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class UpdateTrailers extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Licence';

    public function handleCommand(CommandInterface $command)
    {
        /** @var LicenceEntity $licence */
        $licence = $this->getRepo()->fetchUsingId($command);

        $licence->getOrganisation()->setConfirmShareTrailerInfo($command->getShareInfo());

        $this->getRepo()->save($licence);

        $this->result->addMessage('Licence updated');

        return $this->result;
    }
}
