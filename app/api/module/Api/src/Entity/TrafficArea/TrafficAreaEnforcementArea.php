<?php

namespace Dvsa\Olcs\Api\Entity\TrafficArea;

use Doctrine\ORM\Mapping as ORM;

/**
 * TrafficAreaEnforcementArea Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="traffic_area_enforcement_area",
 *    indexes={
 *        @ORM\Index(name="ix_traffic_area_enforcement_area_traffic_area_id", columns={"traffic_area_id"}),
 *        @ORM\Index(name="ix_traffic_area_enforcement_area_enforcement_area_id", columns={"enforcement_area_id"}),
 *        @ORM\Index(name="ix_traffic_area_enforcement_area_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_traffic_area_enforcement_area_last_modified_by", columns={"last_modified_by"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_ta_enforcement_area_traffic_area_id_enforcement_area_id", columns={"traffic_area_id","enforcement_area_id"})
 *    }
 * )
 */
class TrafficAreaEnforcementArea extends AbstractTrafficAreaEnforcementArea
{
}
