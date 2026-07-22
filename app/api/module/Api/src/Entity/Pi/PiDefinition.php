<?php

namespace Dvsa\Olcs\Api\Entity\Pi;

use Doctrine\ORM\Mapping as ORM;

/**
 * PiDefinition Entity
 */
#[ORM\Table(name: 'pi_definition')]
#[ORM\Index(name: 'ix_pi_definition_goods_or_psv', columns: ['goods_or_psv'])]
#[ORM\Index(name: 'ix_pi_definition_created_by', columns: ['created_by'])]
#[ORM\Index(name: 'ix_pi_definition_last_modified_by', columns: ['last_modified_by'])]
#[ORM\Entity]
class PiDefinition extends AbstractPiDefinition
{
}
