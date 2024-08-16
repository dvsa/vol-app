<?php

namespace Dvsa\Olcs\Api\Entity\EnforcementArea;

use Doctrine\ORM\Mapping as ORM;

/**
 * PostcodeEnforcementArea Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="postcode_enforcement_area",
 *    indexes={
 *        @ORM\Index(name="ix_postcode_enforcement_area_enforcement_area_id", columns={"enforcement_area_id"}),
 *        @ORM\Index(name="ix_postcode_enforcement_area_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_postcode_enforcement_area_last_modified_by", columns={"last_modified_by"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_postcode_enforcement_area_enforcement_area_id_postcode_id", columns={"enforcement_area_id","postcode_id"})
 *    }
 * )
 */
class PostcodeEnforcementArea extends AbstractPostcodeEnforcementArea
{
}
