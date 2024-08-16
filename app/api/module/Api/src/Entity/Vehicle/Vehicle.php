<?php

namespace Dvsa\Olcs\Api\Entity\Vehicle;

use Doctrine\ORM\Mapping as ORM;

/**
 * Vehicle Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="vehicle",
 *    indexes={
 *        @ORM\Index(name="ix_vehicle_vrm", columns={"vrm"}),
 *        @ORM\Index(name="ix_vehicle_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_vehicle_last_modified_by", columns={"last_modified_by"}),
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_vehicle_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
class Vehicle extends AbstractVehicle
{
    public const ERROR_VRM_EXISTS = 'VE-VRM-1';
    public const ERROR_VRM_OTHER_LICENCE = 'VE-VRM-2';
    public const ERROR_TOO_MANY = 'VE-AUTH-1';
    public const ERROR_VRM_HAS_SECTION_26 = 'VE-VRM_SECTION26';
}
