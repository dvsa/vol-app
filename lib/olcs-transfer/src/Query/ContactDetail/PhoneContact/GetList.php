<?php

namespace Dvsa\Olcs\Transfer\Query\ContactDetail\PhoneContact;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\OrderedQueryInterface;
use Dvsa\Olcs\Transfer\Query\OrderedTrait;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/contact-details/phone-contact")
 */
class GetList extends AbstractQuery implements OrderedQueryInterface
{
    use OrderedTrait;

    /**
     * @var int
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $contactDetailsId;

    /**
     * Get Contact Details Id
     *
     * @return int
     */
    public function getContactDetailsId()
    {
        return $this->contactDetailsId;
    }
}
