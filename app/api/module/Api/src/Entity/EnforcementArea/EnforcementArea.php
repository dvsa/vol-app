<?php

namespace Dvsa\Olcs\Api\Entity\EnforcementArea;

use Doctrine\ORM\Mapping as ORM;

/**
 * EnforcementArea Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="enforcement_area",
 *    indexes={
 *        @ORM\Index(name="ix_enforcement_area_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_enforcement_area_last_modified_by", columns={"last_modified_by"})
 *    }
 * )
 */
class EnforcementArea extends AbstractEnforcementArea
{
    public const NORTHERN_IRELAND_ENFORCEMENT_AREA_CODE = 'EA-N';
}
