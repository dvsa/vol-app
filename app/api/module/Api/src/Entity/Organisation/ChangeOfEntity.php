<?php

namespace Dvsa\Olcs\Api\Entity\Organisation;

use Doctrine\ORM\Mapping as ORM;

/**
 * ChangeOfEntity Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="change_of_entity",
 *    indexes={
 *        @ORM\Index(name="ix_change_of_entity_licence_id", columns={"licence_id"}),
 *        @ORM\Index(name="ix_change_of_entity_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_change_of_entity_last_modified_by", columns={"last_modified_by"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_change_of_entity_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
class ChangeOfEntity extends AbstractChangeOfEntity
{
}
