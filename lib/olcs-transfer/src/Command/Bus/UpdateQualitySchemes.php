<?php

/**
 * Bus stops
 */

namespace Dvsa\Olcs\Transfer\Command\Bus;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits as FieldType;

/**
 * @Transfer\RouteName("backend/bus/single/quality")
 * @Transfer\Method("PUT")
 */
final class UpdateQualitySchemes extends AbstractCommand
{
    use FieldType\Identity;
    use FieldType\Version;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     */
    public $isQualityPartnership;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"max":4000})
     * @Transfer\Optional
     */
    protected $qualityPartnershipDetails;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     */
    protected $qualityPartnershipFacilitiesUsed;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     */
    protected $isQualityContract;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"max":4000})
     * @Transfer\Optional
     */
    protected $qualityContractDetails;

    /**
     * @return mixed
     */
    public function getIsQualityPartnership()
    {
        return $this->isQualityPartnership;
    }

    /**
     * @return mixed
     */
    public function getQualityPartnershipDetails()
    {
        return $this->qualityPartnershipDetails;
    }

    /**
     * @return mixed
     */
    public function getQualityPartnershipFacilitiesUsed()
    {
        return $this->qualityPartnershipFacilitiesUsed;
    }

    /**
     * @return mixed
     */
    public function getIsQualityContract()
    {
        return $this->isQualityContract;
    }

    /**
     * @return mixed
     */
    public function getQualityContractDetails()
    {
        return $this->qualityContractDetails;
    }
}
