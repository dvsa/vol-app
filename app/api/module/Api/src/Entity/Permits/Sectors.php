<?php

namespace Dvsa\Olcs\Api\Entity\Permits;

use Doctrine\ORM\Mapping as ORM;

/**
 * Sectors Entity
 */
#[ORM\Table(name: 'sectors')]
#[ORM\Index(name: 'ix_permit_sectors_created_by', columns: ['created_by'])]
#[ORM\Index(name: 'ix_permit_sectors_last_modified_by', columns: ['last_modified_by'])]
#[ORM\Entity]
class Sectors extends AbstractSectors
{
    public const SECTOR_OPTION_NAME__NONE = 'None/More than one of these sectors';
}
