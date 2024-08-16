<?php

/**
 * DisassociateS4.php
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\LicenceOperatingCentre;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre;
use Dvsa\Olcs\Api\Domain\Command\Result;

/**
 * Class DisassociateS4
 *
 * Disassociate a licence operating centre with its corresponding S4 record.
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
final class DisassociateS4 extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'LicenceOperatingCentre';

    public function handleCommand(CommandInterface $command)
    {
        foreach ($command->getLicenceOperatingCentres() as $loc) {
            /* @var $loc LicenceOperatingCentre */
            $loc->setS4(null);
            $this->getRepo()->save($loc);
        }

        $result = new Result();
        $result->addMessage(
            sprintf(
                'S4 flag removed from %d Licence Operating centre(s)',
                count($command->getLicenceOperatingCentres())
            )
        );

        return $result;
    }
}
