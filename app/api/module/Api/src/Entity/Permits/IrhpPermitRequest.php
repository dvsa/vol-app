<?php

namespace Dvsa\Olcs\Api\Entity\Permits;

use Doctrine\ORM\Mapping as ORM;

/**
 * IrhpPermitRequest Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="irhp_permit_request",
 *    indexes={
 *        @ORM\Index(name="ix_irhp_permit_request_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_irhp_permit_request_irhp_application_id",
     *     columns={"irhp_application_id"}),
 *        @ORM\Index(name="ix_irhp_permit_request_irhp_permit_application_id",
     *     columns={"irhp_permit_application_id"}),
 *        @ORM\Index(name="ix_irhp_permit_request_last_modified_by", columns={"last_modified_by"})
 *    }
 * )
 */
class IrhpPermitRequest extends AbstractIrhpPermitRequest
{
}
