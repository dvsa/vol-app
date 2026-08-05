<?php

namespace Dvsa\Olcs\Api\Entity\Inspection;

use Doctrine\ORM\Mapping as ORM;

/**
 * InspectionEmail Entity
 */
#[ORM\Table(name: 'inspection_email')]
#[ORM\Index(name: 'ix_inspection_email_inspection_request_id', columns: ['inspection_request_id'])]
#[ORM\Entity]
class InspectionEmail extends AbstractInspectionEmail
{
}
