<?php

namespace Dvsa\Olcs\Api\Entity\Prohibition;

use Doctrine\ORM\Mapping as ORM;

/**
 * Prohibition Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="prohibition",
 *    indexes={
 *        @ORM\Index(name="ix_prohibition_case_id", columns={"case_id"}),
 *        @ORM\Index(name="ix_prohibition_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_prohibition_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_prohibition_prohibition_type", columns={"prohibition_type"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_prohibition_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
class Prohibition extends AbstractProhibition
{
}
