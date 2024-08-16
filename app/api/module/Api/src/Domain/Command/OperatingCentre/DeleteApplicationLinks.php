<?php

/**
 * Delete Operating Centre Application Links
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Api\Domain\Command\OperatingCentre;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Delete Operating Centre Application Links
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class DeleteApplicationLinks extends AbstractCommand
{
    protected $operatingCentre;

    /**
     * Gets the value of operatingCentre.
     *
     * @return mixed
     */
    public function getOperatingCentre()
    {
        return $this->operatingCentre;
    }
}
