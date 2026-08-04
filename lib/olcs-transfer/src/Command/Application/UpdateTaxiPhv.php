<?php

/**
 * UpdateTaxiPhv
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\Application;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/application/single/taxi-phv")
 * @Transfer\Method("PUT")
 */
final class UpdateTaxiPhv extends AbstractCommand
{
    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $id;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Dvsa\Olcs\Transfer\Validators\TrafficArea")
     * @Transfer\Optional
     */
    protected $trafficArea;

    public function getId()
    {
        return $this->id;
    }

    public function getTrafficArea()
    {
        return $this->trafficArea;
    }
}
