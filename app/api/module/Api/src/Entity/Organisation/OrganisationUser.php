<?php

namespace Dvsa\Olcs\Api\Entity\Organisation;

use Doctrine\ORM\Mapping as ORM;

/**
 * OrganisationUser Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="organisation_user",
 *    indexes={
 *        @ORM\Index(name="ix_organisation_user_user_id", columns={"user_id"}),
 *        @ORM\Index(name="ix_organisation_user_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_organisation_user_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="IDX_CFD7D6519E6B1585", columns={"organisation_id"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_organisation_user_organisation_id_user_id", columns={"organisation_id","user_id"})
 *    }
 * )
 */
class OrganisationUser extends AbstractOrganisationUser
{
}
