<?php

namespace Dvsa\Olcs\Api\Entity\Bus;

use Doctrine\ORM\Mapping as ORM;

/**
 * BusServiceType Entity
 */
#[ORM\Table(name: 'bus_service_type')]
#[ORM\Entity]
class BusServiceType extends AbstractBusServiceType
{
}
