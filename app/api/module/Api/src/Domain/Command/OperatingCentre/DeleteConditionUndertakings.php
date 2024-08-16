<?php

/**
 * Delete Operating Centre Condition Undertaking Links
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Api\Domain\Command\OperatingCentre;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Delete Operating Centre Condition Undertaking Links
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class DeleteConditionUndertakings extends AbstractCommand
{
    protected $operatingCentre;

    protected $licence;

    protected $application;

    /**
     * Gets the value of operatingCentre.
     *
     * @return mixed
     */
    public function getOperatingCentre()
    {
        return $this->operatingCentre;
    }

    /**
     * Gets the value of licence.
     *
     * @return mixed
     */
    public function getLicence()
    {
        return $this->licence;
    }

    /**
     * Gets the value of application.
     *
     * @return mixed
     */
    public function getApplication()
    {
        return $this->application;
    }
}
