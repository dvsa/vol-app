<?php

/**
 * Community Licence / Reprint
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\CommunityLic;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\ApplicationOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\UserOptional;

/**
 * @Transfer\RouteName("backend/community-lic/reprint")
 * @Transfer\Method("POST")
 */
final class Reprint extends AbstractCommand
{
    use ApplicationOptional;

    use UserOptional;

    /**
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    public $licence;

    /**
     * @Transfer\ArrayInput
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    public $communityLicenceIds = [];

    /**
     * @Transfer\Filter("Laminas\Filter\Boolean")
     * @Transfer\Optional
     */
    protected $isBatchReprint;

    /**
     * Get licence ID
     *
     * @return int
     */
    public function getLicence()
    {
        return $this->licence;
    }

    /**
     * Get list of community licence IDs
     *
     * @return array
     */
    public function getCommunityLicenceIds()
    {
        return $this->communityLicenceIds;
    }

    /**
     * Get whether this is a batch reprint
     *
     * @return bool
     */
    public function getIsBatchReprint()
    {
        return $this->isBatchReprint;
    }
}
