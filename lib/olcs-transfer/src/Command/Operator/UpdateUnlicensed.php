<?php

/**
 * Update Unlicensed Operator
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Transfer\Command\Operator;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits as FieldTypeTraits;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/operator-unlicensed/single")
 * @Transfer\Method("PUT")
 */
final class UpdateUnlicensed extends AbstractCommand
{
    use FieldTypeTraits\Identity;
    use FieldTypeTraits\Version;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\NotEmpty")
     */
    protected $name;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"lcat_gv", "lcat_psv"}})
     */
    protected $operatorType;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Dvsa\Olcs\Transfer\Validators\TrafficArea")
     */
    protected $trafficArea;

    /**
     * @Transfer\Partial("Dvsa\Olcs\Transfer\Command\Partial\OperatorContactDetails")
     * @Transfer\Optional
     */
    protected $contactDetails;

    /**
     * Gets the value of name.
     *
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Gets the value of operatorType.
     *
     * @return mixed
     */
    public function getOperatorType()
    {
        return $this->operatorType;
    }

    /**
     * Gets the value of trafficArea.
     *
     * @return mixed
     */
    public function getTrafficArea()
    {
        return $this->trafficArea;
    }

    /**
     * Gets the value of contactDetails.
     *
     * @return mixed
     */
    public function getContactDetails()
    {
        return $this->contactDetails;
    }
}
