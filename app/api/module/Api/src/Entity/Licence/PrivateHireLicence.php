<?php

namespace Dvsa\Olcs\Api\Entity\Licence;

use Doctrine\ORM\Mapping as ORM;

/**
 * PrivateHireLicence Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="private_hire_licence",
 *    indexes={
 *        @ORM\Index(name="ix_private_hire_licence_licence_id", columns={"licence_id"}),
 *        @ORM\Index(name="ix_private_hire_licence_contact_details_id", columns={"contact_details_id"}),
 *        @ORM\Index(name="ix_private_hire_licence_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_private_hire_licence_last_modified_by", columns={"last_modified_by"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_private_hire_licence_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
class PrivateHireLicence extends AbstractPrivateHireLicence
{
}
