<?php

/**
 * DeleteApplicationOperatingCentre.php
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Command\ApplicationOperatingCentre;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Class DeleteApplicationOperatingCentre
 *
 * @package Dvsa\Olcs\Api\Domain\Command\LicenceStatusRule
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
final class DeleteApplicationOperatingCentre extends AbstractCommand
{
    protected $s4 = null;

    /**
     * @return null
     */
    public function getS4()
    {
        return $this->s4;
    }
}
