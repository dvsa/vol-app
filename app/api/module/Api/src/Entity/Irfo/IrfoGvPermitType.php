<?php

namespace Dvsa\Olcs\Api\Entity\Irfo;

use Doctrine\ORM\Mapping as ORM;

/**
 * IrfoGvPermitType Entity
 */
#[ORM\Table(name: 'irfo_gv_permit_type')]
#[ORM\Index(name: 'ix_irfo_gv_permit_type_irfo_country_id', columns: ['irfo_country_id'])]
#[ORM\Index(name: 'ix_irfo_gv_permit_type_created_by', columns: ['created_by'])]
#[ORM\Index(name: 'ix_irfo_gv_permit_type_last_modified_by', columns: ['last_modified_by'])]
#[ORM\Entity]
class IrfoGvPermitType extends AbstractIrfoGvPermitType
{
}
