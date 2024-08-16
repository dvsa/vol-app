<?php

namespace Dvsa\Olcs\Api\Entity\System;

use Doctrine\ORM\Mapping as ORM;

/**
 * Tag Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="tag",
 *    indexes={
 *        @ORM\Index(name="fk_tag_users_created_by", columns={"created_by"}),
 *        @ORM\Index(name="fk_tag_users_last_modified_by", columns={"last_modified_by"})
 *    }
 * )
 */
class Tag extends AbstractTag
{
}
