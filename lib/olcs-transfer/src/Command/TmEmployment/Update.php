<?php

/**
 * Update TmEmployment
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\TmEmployment;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/tm-employment/single")
 * @Transfer\Method("PUT")
 */
final class Update extends AbstractCommand
{
    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $id;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $version;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":0,"max":45})
     * @Transfer\Optional
     */
    protected $position;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":0,"max":300})
     * @Transfer\Optional
     */
    protected $hoursPerWeek;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":0,"max":90})
     */
    protected $employerName;

    /**
    * @Transfer\Partial("Dvsa\Olcs\Transfer\Command\Partial\AddressOptional")
    */
    protected $address;

    /**
     * Get TmEmployment ID
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     *
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     *
     * @return string
     */
    public function getHoursPerWeek()
    {
        return $this->hoursPerWeek;
    }

    /**
     *
     * @return string
     */
    public function getEmployerName()
    {
        return $this->employerName;
    }

    /**
     * @return \Dvsa\Olcs\Transfer\Command\Partial\Address
     */
    public function getAddress()
    {
        return $this->address;
    }
}
