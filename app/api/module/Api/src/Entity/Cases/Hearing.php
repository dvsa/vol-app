<?php

namespace Dvsa\Olcs\Api\Entity\Cases;

use Doctrine\ORM\Mapping as ORM;

/**
 * Hearing Entity
 */
#[ORM\Table(name: 'hearing')]
#[ORM\Index(name: 'ix_hearing_case_id', columns: ['case_id'])]
#[ORM\Index(name: 'ix_hearing_venue_id', columns: ['venue_id'])]
#[ORM\Index(name: 'ix_hearing_created_by', columns: ['created_by'])]
#[ORM\Index(name: 'ix_hearing_last_modified_by', columns: ['last_modified_by'])]
#[ORM\Index(name: 'ix_hearing_presiding_tc_id', columns: ['presiding_tc_id'])]
#[ORM\Index(name: 'ix_hearing_hearing_type', columns: ['hearing_type'])]
#[ORM\Entity]
class Hearing extends AbstractHearing
{
}
