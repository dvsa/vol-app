<?php

/**
 * AssociateS4.php
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Command\LicenceOperatingCentre;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Class AssociateS4
 *
 * @package Dvsa\Olcs\Api\Domain\Command\LicenceStatusRule
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
final class AssociateS4 extends AbstractCommand
{
    protected $s4;

    protected $licenceOperatingCentres;

    /**
     * @return mixed
     */
    public function getS4()
    {
        return $this->s4;
    }

    /**
     * @return mixed
     */
    public function getLicenceOperatingCentres()
    {
        return $this->licenceOperatingCentres;
    }
}
