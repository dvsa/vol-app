<?php

namespace Dvsa\Olcs\Api\Entity\Licence;

use Doctrine\ORM\Mapping as ORM;

/**
 * Trailer Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="trailer",
 *    indexes={
 *        @ORM\Index(name="ix_trailer_licence_id", columns={"licence_id"}),
 *        @ORM\Index(name="ix_trailer_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_trailer_last_modified_by", columns={"last_modified_by"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_trailer_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
class Trailer extends AbstractTrailer implements \Dvsa\Olcs\Api\Entity\OrganisationProviderInterface
{
    public function getRelatedOrganisation()
    {
        if (!$this->getLicence()) {
            return null;
        }

        return $this->getLicence()->getOrganisation();
    }
}
