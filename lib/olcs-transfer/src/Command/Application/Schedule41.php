<?php

/**
 * Schedule41.php
 *
 * @author Joshua Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\Application;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Licence;

/**
 * @Transfer\RouteName("backend/application/single/schedule-41")
 * @Transfer\Method("PUT")
 */
final class Schedule41 extends AbstractCommand
{
    use Identity;
    use Licence;

    /**
     * @Transfer\ArrayInput
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $operatingCentres = [];

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Dvsa\Olcs\Transfer\Validators\YesNo")
     * @Transfer\Optional
     */
    protected $surrenderLicence;

    /**
     * Get operating centres ids
     *
     * @return array
     */
    public function getOperatingCentres()
    {
        return $this->operatingCentres;
    }

    /**
     * Get surrender licence
     *
     * @return string
     */
    public function getSurrenderLicence()
    {
        return $this->surrenderLicence;
    }
}
