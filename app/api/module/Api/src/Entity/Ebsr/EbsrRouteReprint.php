<?php

namespace Dvsa\Olcs\Api\Entity\Ebsr;

use Doctrine\ORM\Mapping as ORM;

/**
 * EbsrRouteReprint Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="ebsr_route_reprint",
 *    indexes={
 *        @ORM\Index(name="ix_ebsr_route_reprint_bus_reg_id", columns={"bus_reg_id"}),
 *        @ORM\Index(name="ix_ebsr_route_reprint_requested_user_id", columns={"requested_user_id"}),
 *        @ORM\Index(name="ix_ebsr_route_reprint_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
class EbsrRouteReprint extends AbstractEbsrRouteReprint
{
}
