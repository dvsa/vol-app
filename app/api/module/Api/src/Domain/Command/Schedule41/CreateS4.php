<?php

/**
 * CreateS4.php
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Command\Schedule41;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Create S4 record.
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
final class CreateS4 extends AbstractCommand
{
    protected $application;

    protected $licence;

    protected $surrenderLicence;

    protected $receivedDate;

    /**
     * @return mixed
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * @return mixed
     */
    public function getLicence()
    {
        return $this->licence;
    }

    /**
     * @return mixed
     */
    public function getSurrenderLicence()
    {
        return $this->surrenderLicence;
    }

    /**
     * @return mixed
     */
    public function getReceivedDate()
    {
        return $this->receivedDate;
    }
}
