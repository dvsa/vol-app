<?php

namespace Dvsa\Olcs\Api\Entity\Legacy;

use Doctrine\ORM\Mapping as ORM;

/**
 * LegacyPiReason Entity
 */
#[ORM\Table(name: 'legacy_pi_reason')]
#[ORM\Index(name: 'ix_legacy_pi_reason_created_by', columns: ['created_by'])]
#[ORM\Index(name: 'ix_legacy_pi_reason_last_modified_by', columns: ['last_modified_by'])]
#[ORM\Entity]
class LegacyPiReason extends AbstractLegacyPiReason
{
}
