<?php

/**
 * Set Default Traffic Area And Enforcement Area
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Command\Application;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

/**
 * Set Default Traffic Area And Enforcement Area
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class SetDefaultTrafficAreaAndEnforcementArea extends AbstractCommand
{
    use Identity;

    protected $operatingCentre;

    protected $postcode;

    /**
     * @return mixed
     */
    public function getOperatingCentre()
    {
        return $this->operatingCentre;
    }

    /**
     * @return string
     */
    public function getPostcode()
    {
        return $this->postcode;
    }
}
