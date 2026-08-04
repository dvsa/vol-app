<?php

/**
 * Update IRHP Permit Jurisdiction
 */

namespace Dvsa\Olcs\Transfer\Command\IrhpPermitJurisdiction;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/irhp-permit-jurisdiction")
 * @Transfer\Method("PUT")
 */
final class Update extends AbstractCommand
{
    /**
     * @var array
     * @Transfer\ArrayInput
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": -1})
     */
    protected $trafficAreas;

    /**
     * @return array
     */
    public function getTrafficAreas(): array
    {
        return $this->trafficAreas;
    }
}
