<?php

/**
 * Create Partner
 */

namespace Dvsa\Olcs\Transfer\Command\User;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/partner")
 * @Transfer\Method("POST")
 */
final class CreatePartner extends AbstractCommand
{
    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"min":3,"max":35})
     */
    protected $description;

    /**
     * @Transfer\Partial("Dvsa\Olcs\Transfer\Command\Partial\Address")
     */
    protected $address;

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return mixed
     */
    public function getAddress()
    {
        return $this->address;
    }
}
