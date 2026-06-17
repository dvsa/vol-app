<?php

namespace Dvsa\Olcs\Transfer\Query\ContactDetail\PhoneContact;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * @Transfer\RouteName("backend/contact-details/phone-contact/single")
 */
class Get extends AbstractQuery
{
    use Identity;
}
