<?php

/**
 * User List Selfserve
 */

namespace Dvsa\Olcs\Api\Domain\Query\User;

use Dvsa\Olcs\Transfer\FieldType\Traits\LastLoggedInFromOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\OrganisationOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\RolesOptional;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\OrderedQueryInterface;
use Dvsa\Olcs\Transfer\Query\OrderedTrait;
use Dvsa\Olcs\Transfer\Query\PagedQueryInterface;
use Dvsa\Olcs\Transfer\Query\PagedTrait;

/**
 * User List Selfserve
 */
final class UserListSelfserve extends AbstractQuery implements PagedQueryInterface, OrderedQueryInterface
{
    use PagedTrait;
    use OrderedTrait;
    use RolesOptional;
    use OrganisationOptional;
    use LastLoggedInFromOptional;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     * @Transfer\Optional
     */
    protected $localAuthority = null;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     * @Transfer\Optional
     */
    protected $partnerContactDetails = null;

    /**
     * @return int
     */
    public function getLocalAuthority()
    {
        return $this->localAuthority;
    }

    /**
     * @return int
     */
    public function getPartnerContactDetails()
    {
        return $this->partnerContactDetails;
    }
}
