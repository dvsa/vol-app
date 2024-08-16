<?php

namespace Dvsa\Olcs\Api\Entity\Prohibition;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProhibitionDefect Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="prohibition_defect",
 *    indexes={
 *        @ORM\Index(name="ix_prohibition_defect_prohibition_id", columns={"prohibition_id"}),
 *        @ORM\Index(name="ix_prohibition_defect_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_prohibition_defect_last_modified_by", columns={"last_modified_by"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_prohibition_defect_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
class ProhibitionDefect extends AbstractProhibitionDefect
{
}
