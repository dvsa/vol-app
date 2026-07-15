<?php

namespace Dvsa\Olcs\Api\Entity\Si;

use Doctrine\ORM\Mapping as ORM;

/**
 * SiPenaltyRequestedType Entity
 */
#[ORM\Table(name: 'si_penalty_requested_type')]
#[ORM\Index(name: 'ix_si_penalty_requested_type_created_by', columns: ['created_by'])]
#[ORM\Index(name: 'ix_si_penalty_requested_type_last_modified_by', columns: ['last_modified_by'])]
#[ORM\Entity]
class SiPenaltyRequestedType extends AbstractSiPenaltyRequestedType
{
}
