<?php

/**
 * DisassociateS4.php
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Command\LicenceOperatingCentre;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Class DisassociateS4
 *
 * @package Dvsa\Olcs\Api\Domain\Command\LicenceOperatingCentre
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
final class DisassociateS4 extends AbstractCommand
{
    protected $licenceOperatingCentres;

    /**
     * @return mixed
     */
    public function getLicenceOperatingCentres()
    {
        return $this->licenceOperatingCentres;
    }
}
