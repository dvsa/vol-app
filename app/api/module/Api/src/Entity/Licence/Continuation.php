<?php

namespace Dvsa\Olcs\Api\Entity\Licence;

use Doctrine\ORM\Mapping as ORM;

/**
 * Continuation Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="continuation",
 *    indexes={
 *        @ORM\Index(name="ix_continuation_month", columns={"month"}),
 *        @ORM\Index(name="ix_continuation_year", columns={"year"}),
 *        @ORM\Index(name="ix_continuation_traffic_area_id", columns={"traffic_area_id"}),
 *        @ORM\Index(name="ix_continuation_created_by", columns={"created_by"})
 *    }
 * )
 */
class Continuation extends AbstractContinuation
{
}
