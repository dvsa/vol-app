<?php

namespace Dvsa\Olcs\Api\Entity\Licence;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre;
use Dvsa\Olcs\Api\Entity\Application\S4;
use Dvsa\Olcs\Api\Entity\OrganisationProviderInterface;

/**
 * LicenceOperatingCentre Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="licence_operating_centre",
 *    indexes={
 *        @ORM\Index(name="ix_licence_operating_centre_licence_id", columns={"licence_id"}),
 *        @ORM\Index(name="ix_licence_operating_centre_operating_centre_id", columns={"operating_centre_id"}),
 *        @ORM\Index(name="ix_licence_operating_centre_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_licence_operating_centre_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_licence_operating_centre_s4_id", columns={"s4_id"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_licence_operating_centre_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
class LicenceOperatingCentre extends AbstractLicenceOperatingCentre implements OrganisationProviderInterface
{
    public function __construct(Licence $licence, OperatingCentre $operatingCentre)
    {
        $this->setLicence($licence);
        $this->setOperatingCentre($operatingCentre);
    }

    /**
     * Can this LOC be deleted
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
        return $this->getLicence()->getRelatedOrganisation();
    }
}
