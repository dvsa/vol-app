<?php

namespace Dvsa\Olcs\Api\Entity\Si;

use Doctrine\ORM\Mapping as ORM;

/**
 * SiPenaltyImposedType Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="si_penalty_imposed_type",
 *    indexes={
 *        @ORM\Index(name="ix_si_penalty_imposed_type_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_si_penalty_imposed_type_last_modified_by", columns={"last_modified_by"})
 *    }
 * )
 */
class SiPenaltyImposedType extends AbstractSiPenaltyImposedType
{
}
