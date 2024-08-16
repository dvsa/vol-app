<?php

namespace Dvsa\Olcs\Api\Entity\Cases;

use Doctrine\ORM\Mapping as ORM;

/**
 * Hearing Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="hearing",
 *    indexes={
 *        @ORM\Index(name="ix_hearing_case_id", columns={"case_id"}),
 *        @ORM\Index(name="ix_hearing_venue_id", columns={"venue_id"}),
 *        @ORM\Index(name="ix_hearing_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_hearing_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_hearing_presiding_tc_id", columns={"presiding_tc_id"}),
 *        @ORM\Index(name="ix_hearing_hearing_type", columns={"hearing_type"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_hearing_olbs_key_olbs_type", columns={"olbs_key","olbs_type"})
 *    }
 * )
 */
class Hearing extends AbstractHearing
{
}
