<?php

/**
 * Community Licence / Restore
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\CommunityLic;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/community-lic/restore")
 * @Transfer\Method("POST")
 */
final class Restore extends AbstractCommand
{
    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
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

    public function getLicence()
    {
        return $this->licence;
    }

    public function getCommunityLicenceIds()
    {
        return $this->communityLicenceIds;
    }
}
