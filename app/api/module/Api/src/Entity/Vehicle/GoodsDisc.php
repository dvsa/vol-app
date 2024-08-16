<?php

namespace Dvsa\Olcs\Api\Entity\Vehicle;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle;

/**
 * GoodsDisc Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="goods_disc",
 *    indexes={
 *        @ORM\Index(name="ix_goods_disc_licence_vehicle_id", columns={"licence_vehicle_id"}),
 *        @ORM\Index(name="ix_goods_disc_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_goods_disc_last_modified_by", columns={"last_modified_by"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_goods_disc_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
class GoodsDisc extends AbstractGoodsDisc
{
    public const ERROR_NO_DISCS_TO_PRINT = 'err_no_discs';

    public function __construct(LicenceVehicle $licenceVehicle)
    {
        $this->setLicenceVehicle($licenceVehicle);
    }
}
