<?php

/**
 * RemoveTransportManagerLicence.php
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Command\Tm;

use Dvsa\Olcs\Api\Domain\Command\AbstractIdOnlyCommand;

/**
 * Class RemoveLicenceVehicle
 *
 * Remove transport manager from licence record.
 *
 * @package Dvsa\Olcs\Api\Domain\Command\Discs
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
final class DeleteTransportManagerLicence extends AbstractIdOnlyCommand
{
    protected $licence;

    /**
     * @return mixed
     */
    public function getLicence()
    {
        return $this->licence;
    }
}
