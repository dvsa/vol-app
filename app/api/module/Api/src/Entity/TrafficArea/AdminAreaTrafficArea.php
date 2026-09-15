<?php

namespace Dvsa\Olcs\Api\Entity\TrafficArea;

use Doctrine\ORM\Mapping as ORM;

/**
 * AdminAreaTrafficArea Entity
 */
#[ORM\Table(name: 'admin_area_traffic_area')]
#[ORM\Index(name: 'ix_admin_area_traffic_area_traffic_area_id', columns: ['traffic_area_id'])]
#[ORM\Index(name: 'ix_admin_area_traffic_area_created_by', columns: ['created_by'])]
#[ORM\Index(name: 'ix_admin_area_traffic_area_last_modified_by', columns: ['last_modified_by'])]
#[ORM\Entity]
class AdminAreaTrafficArea extends AbstractAdminAreaTrafficArea
{
}
