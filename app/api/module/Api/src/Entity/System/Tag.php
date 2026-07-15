<?php

namespace Dvsa\Olcs\Api\Entity\System;

use Doctrine\ORM\Mapping as ORM;

/**
 * Tag Entity
 */
#[ORM\Table(name: 'tag')]
#[ORM\Index(name: 'fk_tag_users_created_by', columns: ['created_by'])]
#[ORM\Index(name: 'fk_tag_users_last_modified_by', columns: ['last_modified_by'])]
#[ORM\Entity]
class Tag extends AbstractTag
{
}
