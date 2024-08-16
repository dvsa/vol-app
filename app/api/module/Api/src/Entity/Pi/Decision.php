<?php

namespace Dvsa\Olcs\Api\Entity\Pi;

use Doctrine\ORM\Mapping as ORM;

/**
 * Decision Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="decision",
 *    indexes={
 *        @ORM\Index(name="ix_decision_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_decision_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_decision_goods_or_psv", columns={"goods_or_psv"})
 *    }
 * )
 */
class Decision extends AbstractDecision
{
}
