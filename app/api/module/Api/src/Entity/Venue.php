<?php

namespace Dvsa\Olcs\Api\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Venue Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="venue",
 *    indexes={
 *        @ORM\Index(name="ix_venue_address_id", columns={"address_id"}),
 *        @ORM\Index(name="ix_venue_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_venue_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_venue_traffic_area_id", columns={"traffic_area_id"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_venue_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
class Venue extends AbstractVenue
{
}
