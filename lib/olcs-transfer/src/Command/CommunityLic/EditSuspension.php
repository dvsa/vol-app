<?php

/**
 * Community Licence / Edit supsension
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\CommunityLic;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits as FieldTypeTraits;

/**
 * @Transfer\RouteName("backend/community-lic/edit-suspension/single")
 * @Transfer\Method("PUT")
 */
final class EditSuspension extends AbstractCommand
{
    use FieldTypeTraits\Identity;
    use FieldTypeTraits\Version;

    /**
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $communityLicenceId;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"cl_sts_active", "cl_sts_suspended"}})
     */
    protected $status;

    /**
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     * @Transfer\Optional
     */
    protected $startDate;

    /**
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     * @Transfer\Optional
     */
    protected $endDate;

    /**
     * @Transfer\ArrayInput
     * @Transfer\ArrayValidator("Laminas\Validator\NotEmpty")
     * @Transfer\ArrayFilter("Dvsa\Olcs\Transfer\Filter\FilterEmptyItems")
     * @Transfer\ArrayFilter("Dvsa\Olcs\Transfer\Filter\UniqueItems")
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     */
    protected $reasons = [];

    /**
     * Get community licence id
     *
     * @return int
     */
    public function getCommunityLicenceId()
    {
        return $this->communityLicenceId;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Get start date
     *
     * @return string
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Get end date
     *
     * @return string
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Get reasons
     *
     * @return array
     */
    public function getReasons()
    {
        return $this->reasons;
    }
}
