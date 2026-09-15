<?php

namespace Dvsa\Olcs\Api\Entity\Pi;

use Doctrine\ORM\Mapping as ORM;

/**
 * Reason Entity
 */
#[ORM\Table(name: 'reason')]
#[ORM\Index(name: 'ix_reason_created_by', columns: ['created_by'])]
#[ORM\Index(name: 'ix_reason_last_modified_by', columns: ['last_modified_by'])]
#[ORM\Index(name: 'ix_reason_goods_or_psv', columns: ['goods_or_psv'])]
#[ORM\Entity]
class Reason extends AbstractReason
{
}
