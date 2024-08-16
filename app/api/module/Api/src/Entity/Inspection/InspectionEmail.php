<?php

namespace Dvsa\Olcs\Api\Entity\Inspection;

use Doctrine\ORM\Mapping as ORM;

/**
 * InspectionEmail Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="inspection_email",
 *    indexes={
 *        @ORM\Index(name="ix_inspection_email_inspection_request_id", columns={"inspection_request_id"})
 *    }
 * )
 */
class InspectionEmail extends AbstractInspectionEmail
{
}
