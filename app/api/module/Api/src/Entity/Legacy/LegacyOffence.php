<?php

namespace Dvsa\Olcs\Api\Entity\Legacy;

use Doctrine\ORM\Mapping as ORM;

/**
 * LegacyOffence Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="legacy_offence",
 *    indexes={
 *        @ORM\Index(name="ix_legacy_offence_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_legacy_offence_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="fk_legacy_offence_cases1_idx", columns={"case_id"})
 *    }
 * )
 */
class LegacyOffence extends AbstractLegacyOffence
{
}
