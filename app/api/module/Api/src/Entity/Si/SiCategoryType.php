<?php

namespace Dvsa\Olcs\Api\Entity\Si;

use Doctrine\ORM\Mapping as ORM;

/**
 * SiCategoryType Entity
 */
#[ORM\Table(name: 'si_category_type')]
#[ORM\Index(name: 'ix_si_category_type_si_category_id', columns: ['si_category_id'])]
#[ORM\Index(name: 'ix_si_category_type_created_by', columns: ['created_by'])]
#[ORM\Index(name: 'ix_si_category_type_last_modified_by', columns: ['last_modified_by'])]
#[ORM\Entity]
class SiCategoryType extends AbstractSiCategoryType
{
}
