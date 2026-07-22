<?php

namespace Dvsa\Olcs\Api\Entity\Si;

use Doctrine\ORM\Mapping as ORM;

/**
 * SiCategory Entity
 */
#[ORM\Table(name: 'si_category')]
#[ORM\Index(name: 'ix_si_category_created_by', columns: ['created_by'])]
#[ORM\Index(name: 'ix_si_category_last_modified_by', columns: ['last_modified_by'])]
#[ORM\Entity]
class SiCategory extends AbstractSiCategory
{
    public const ERRU_DEFAULT_CATEGORY = 'MSI';
}
