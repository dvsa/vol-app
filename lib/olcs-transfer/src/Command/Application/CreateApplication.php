<?php

/**
 * Create Application
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\Application;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\LgvDeclarationConfirmation;
use Dvsa\Olcs\Transfer\FieldType\Traits\VehicleType;

/**
 * @Transfer\RouteName("backend/application")
 * @Transfer\Method("POST")
 */
final class CreateApplication extends AbstractCommand
{
    use LgvDeclarationConfirmation;
    use VehicleType;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"lcat_gv","lcat_psv"}})
     * @Transfer\Optional
     */
    protected $operatorType;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"ltyp_r","ltyp_sn","ltyp_si","ltyp_sr"}})
     * @Transfer\Optional
     */
    protected $licenceType;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     * @Transfer\Optional
     */
    protected $niFlag;

    /**
     * @todo add validators
     * @Transfer\Optional
     */
    protected $receivedDate;

    /**
     * @todo add validators
     * @Transfer\Optional
     */
    protected $trafficArea;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $organisation;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"applied_via_post","applied_via_phone"}})
     * @Transfer\Optional
     */
    protected $appliedVia;

    /**
     * @return mixed
     */
    public function getOperatorType()
    {
        return $this->operatorType;
    }

    public function setOperatorType(mixed $operatorType)
    {
        $this->operatorType = $operatorType;
    }

    /**
     * @return mixed
     */
    public function getLicenceType()
    {
        return $this->licenceType;
    }

    public function setLicenceType(mixed $licenceType)
    {
        $this->licenceType = $licenceType;
    }

    /**
     * @return mixed
     */
    public function getNiFlag()
    {
        return $this->niFlag;
    }

    public function setNiFlag(mixed $niFlag)
    {
        $this->niFlag = $niFlag;
    }

    /**
     * @return mixed
     */
    public function getOrganisation()
    {
        return $this->organisation;
    }

    public function setOrganisation(mixed $organisation)
    {
        $this->organisation = $organisation;
    }

    /**
     * @return mixed
     */
    public function getReceivedDate()
    {
        return $this->receivedDate;
    }

    public function setReceivedDate(mixed $receivedDate)
    {
        $this->receivedDate = $receivedDate;
    }

    /**
     * @return mixed
     */
    public function getTrafficArea()
    {
        return $this->trafficArea;
    }

    public function setTrafficArea(mixed $trafficArea)
    {
        $this->trafficArea = $trafficArea;
    }

    /**
     * @return mixed
     */
    public function getAppliedVia()
    {
        return $this->appliedVia;
    }
}
