<?php

namespace Dvsa\Olcs\Api\Entity\Irfo;

use Doctrine\ORM\Mapping as ORM;

/**
 * IrfoVehicle Entity
 */
#[ORM\Table(name: 'irfo_vehicle')]
#[ORM\Index(name: 'ix_irfo_vehicle_created_by', columns: ['created_by'])]
#[ORM\Index(name: 'ix_irfo_vehicle_last_modified_by', columns: ['last_modified_by'])]
#[ORM\Index(name: 'ix_irfo_vehicle_irfo_gv_permit_id', columns: ['irfo_gv_permit_id'])]
#[ORM\UniqueConstraint(name: 'uk_irfo_vehicle_olbs_key', columns: ['olbs_key'])]
#[ORM\Entity]
class IrfoVehicle extends AbstractIrfoVehicle
{
}
