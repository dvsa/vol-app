<?php

namespace Dvsa\Olcs\Api\Entity\Licence;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\OrganisationProviderInterface;
use Dvsa\Olcs\Api\Entity\Vehicle\Vehicle;
use Dvsa\Olcs\Api\Entity\Vehicle\GoodsDisc;

/**
 * LicenceVehicle Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="licence_vehicle",
 *    indexes={
 *        @ORM\Index(name="ix_licence_vehicle_vehicle_id", columns={"vehicle_id"}),
 *        @ORM\Index(name="ix_licence_vehicle_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_licence_vehicle_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_licence_vehicle_application_id", columns={"application_id"}),
 *        @ORM\Index(name="ix_licence_vehicle_interim_application_id", columns={"interim_application_id"}),
 *        @ORM\Index(name="fk_licence_vehicle_licence_id_licence_id", columns={"licence_id"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_licence_vehicle_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
class LicenceVehicle extends AbstractLicenceVehicle implements OrganisationProviderInterface
{
    public function __construct(Licence $licence, Vehicle $vehicle)
    {
        parent::__construct();

        $this->setLicence($licence);
        $this->setVehicle($vehicle);
    }

    /**
     * Get active disc
     *
     * @return GoodsDisc|null
     */
    public function getActiveDisc()
    {
        $goodsDiscs = $this->getGoodsDiscs();

        if ($goodsDiscs->isEmpty()) {
            return null;
        }

        foreach ($goodsDiscs as $goodsDisc) {
            if ($goodsDisc->getCeasedDate() === null) {
                return $goodsDisc;
            }
        }

        return null;
    }

    /**
     * Remove duplicate mark
     *
     * @param bool $shouldRemoveSentDate shouldRemoveSentDate
     *
     * @return void
     */
    public function removeDuplicateMark($shouldRemoveSentDate = false)
    {
        $this->setWarningLetterSeedDate(null);
        if ($shouldRemoveSentDate) {
            $this->setWarningLetterSentDate(null);
        }
    }

    public function markAsDuplicate()
    {
        $this->setWarningLetterSeedDate(new DateTime());
        $this->setWarningLetterSentDate(null);
    }

    public function updateDuplicateMark()
    {
        if ($this->getWarningLetterSeedDate() === null || $this->getWarningLetterSentDate() !== null) {
            $this->markAsDuplicate();
        }
    }

    /**
     * @inheritdoc
     */
    public function getRelatedOrganisation()
    {
        return $this->getLicence()->getRelatedOrganisation();
    }
}
