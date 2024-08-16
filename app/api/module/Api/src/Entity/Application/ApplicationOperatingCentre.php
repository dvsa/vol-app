<?php

namespace Dvsa\Olcs\Api\Entity\Application;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre;
use Dvsa\Olcs\Api\Entity\OrganisationProviderInterface;

/**
 * ApplicationOperatingCentre Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="application_operating_centre",
 *    indexes={
 *        @ORM\Index(name="ix_application_operating_centre_application_id", columns={"application_id"}),
 *        @ORM\Index(name="ix_application_operating_centre_operating_centre_id", columns={"operating_centre_id"}),
 *        @ORM\Index(name="ix_application_operating_centre_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_application_operating_centre_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_application_operating_centre_s4_id", columns={"s4_id"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_application_operating_centre_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
class ApplicationOperatingCentre extends AbstractApplicationOperatingCentre implements OrganisationProviderInterface
{
    public const ACTION_ADD    = 'A';
    public const ACTION_UPDATE = 'U';
    public const ACTION_DELETE = 'D';
    public const AD_POST = 0;
    public const AD_UPLOAD_NOW = 1;
    public const AD_UPLOAD_LATER = 2;

    public function __construct(Application $application, OperatingCentre $operatingCentre)
    {
        $this->application = $application;
        $this->operatingCentre = $operatingCentre;
    }

    /**
     * Can this AOC be deleted
     *
     * @return array empty array means it can be deleted
     */
    public function checkCanDelete()
    {
        $messages = [];
        if ($this->getS4() !== null) {
            // if has an S4 and outcome is empty or outcome is refused, then CANNOT delete
            if ($this->getS4()->getOutcome() === null || $this->getS4()->getOutcome()->getId() !== S4::STATUS_REFUSED) {
                $messages['OC_CANNOT_DELETE_HAS_S4'] = 'Cannot be deleted as it is linked to an S4 record';
            }
        }

        return $messages;
    }

    /**
     * @inheritdoc
     */
    public function getRelatedOrganisation()
    {
        return $this->getApplication()->getRelatedOrganisation();
    }
}
