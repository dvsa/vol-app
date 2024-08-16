<?php

namespace Dvsa\Olcs\Api\Entity\Publication;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink as PublicationLinkEntity;
use Dvsa\Olcs\Api\Entity\Person\Person as PersonEntity;

/**
 * PublicationPoliceData Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="publication_police_data",
 *    indexes={
 *        @ORM\Index(name="ix_publication_police_data_publication_link_id", columns={"publication_link_id"}),
 *        @ORM\Index(name="ix_publication_police_data_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_publication_police_data_last_modified_by", columns={"last_modified_by"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_publication_police_data_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
class PublicationPoliceData extends AbstractPublicationPoliceData
{
    /**
     * Creates a new entity
     */
    public function __construct(PublicationLinkEntity $publicationLink, PersonEntity $person)
    {
        $this->create($publicationLink, $person);
    }

    /**
     * Creates a new entity
     *
     * @note we appear to duplicate data already held in the person entity, however this is intended behaviour since
     * we're storing a snapshot of a fixed point in time
     */
    private function create(PublicationLinkEntity $publicationLink, PersonEntity $person)
    {
        $this->publicationLink = $publicationLink;
        $this->person = $person;
        $this->birthDate = $person->getBirthDate();
        $this->forename = $person->getForename();
        $this->familyName = $person->getFamilyName();
    }
}
