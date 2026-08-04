<?php

namespace Dvsa\Olcs\Transfer\Command\CommunityLic;

use Dvsa\Olcs\Transfer\FieldType\Traits\ApplicationOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\Licence;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/community-lic/annul")
 * @Transfer\Method("POST")
 */
final class Annul extends AbstractCommand
{
    use ApplicationOptional;
    use Licence;

    /**
     * @var array
     * @Transfer\ArrayInput
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    public $communityLicenceIds = [];

    /**
     * @var boolean
     * @Transfer\Optional
     */
    public $checkOfficeCopy;

    /**
     * Get Community Licence Ids
     *
     * @return array
     */
    public function getCommunityLicenceIds()
    {
        return $this->communityLicenceIds;
    }

    /**
     * Get Check Office Copy
     *
     * @return boolean
     */
    public function getCheckOfficeCopy()
    {
        return $this->checkOfficeCopy;
    }
}
