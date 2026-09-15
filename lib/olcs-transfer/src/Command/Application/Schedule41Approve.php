<?php

/**
 * Schedule41Approve.php
 *
 * @author Joshua Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\Application;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/application/single/approve-schedule-41")
 * @Transfer\Method("PUT")
 */
final class Schedule41Approve extends AbstractCommand
{
    use Identity;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Dvsa\Olcs\Transfer\Validators\YesNo")
     */
    protected $trueS4 = null;

    /**
     * @return null
     */
    public function getTrueS4()
    {
        return $this->trueS4;
    }
}
