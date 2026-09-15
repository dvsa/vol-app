<?php

namespace Dvsa\Olcs\Api\Entity\Licence;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;

/**
 * Workshop Entity
 */
#[ORM\Table(name: 'workshop')]
#[ORM\Index(name: 'ix_workshop_licence_id', columns: ['licence_id'])]
#[ORM\Index(name: 'ix_workshop_created_by', columns: ['created_by'])]
#[ORM\Index(name: 'ix_workshop_last_modified_by', columns: ['last_modified_by'])]
#[ORM\Index(name: 'ix_workshop_contact_details_id', columns: ['contact_details_id'])]
#[ORM\UniqueConstraint(name: 'uk_workshop_olbs_key', columns: ['olbs_key'])]
#[ORM\Entity]
class Workshop extends AbstractWorkshop
{
    public function __construct(Licence $licence, ContactDetails $contactDetails)
    {
        $this->setLicence($licence);
        $this->setContactDetails($contactDetails);
    }

    #[\Override]
    protected function getCalculatedValues()
    {
        return ['licence' => null];
    }
}
