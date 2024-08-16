<?php

namespace Dvsa\Olcs\Api\Entity\Person;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Entity\OrganisationProviderInterface;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\Organisation\Disqualification;

/**
 * Person Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="person",
 *    indexes={
 *        @ORM\Index(name="ix_person_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_person_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_person_family_name", columns={"family_name"}),
 *        @ORM\Index(name="ix_person_forename", columns={"forename"}),
 *        @ORM\Index(name="ix_person_title", columns={"title"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_person_olbs_key_olbs_type", columns={"olbs_key","olbs_type"})
 *    }
 * )
 */
class Person extends AbstractPerson implements OrganisationProviderInterface
{
    /**
     * Update person details
     *
     * @param string       $forename   First Name
     * @param string       $familyName Surname
     * @param RefData|null $title      Title
     * @param string|null  $birthDate  DoB
     * @param string|null  $birthPlace Place of Birth
     *
     * @return $this
     */
    public function updatePerson($forename, $familyName, RefData $title = null, $birthDate = null, $birthPlace = null)
    {
        $this->setForename($forename);
        $this->setFamilyName($familyName);

        $this->setTitle($title);

        if ($birthDate !== null) {
            if ($birthDate === '') {
                $this->setBirthDate(null);
            } else {
                $this->setBirthDate(new \DateTime($birthDate));
            }
        }

        if ($birthPlace !== null) {
            $this->setBirthPlace($birthPlace);
        }

        return $this;
    }

    /**
     * Get the ContactDetail entity for this person
     * NB The DB schema does allow multiple contactDetails per person, if this is the case we always take the first;
     *
     * @return false|\Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails
     */
    public function getContactDetail()
    {
        return parent::getContactDetails()->first();
    }

    /**
     * Get calculated values when serialized
     *
     * @return array
     */
    protected function getCalculatedBundleValues()
    {
        return ['disqualificationStatus' => $this->getDisqualificationStatus()];
    }

    /**
     * Get full name "forename familyname"
     *
     * @return string
     */
    public function getFullName()
    {
        return trim($this->getForename() . ' ' . $this->getFamilyName());
    }

    /**
     * Get the list of organisations related to the person record
     *
     * @return array
     */
    public function getRelatedOrganisation()
    {
        $list = [];

        /** @var $orgPerson \Dvsa\Olcs\Api\Entity\Organisation\OrganisationPerson */
        foreach ($this->getOrganisationPersons() as $orgPerson) {
            $org = $orgPerson->getOrganisation();
            $list[$org->getId()] = $org;
        }

        /** @var $appOrgPerson \Dvsa\Olcs\Api\Entity\Application\ApplicationOrganisationPerson */
        foreach ($this->getApplicationOrganisationPersons() as $appOrgPerson) {
            $org = $appOrgPerson->getOrganisation();
            $list[$org->getId()] = $org;
        }
        return $list;
    }

    /**
     * Get the disqualification linked to this contact details
     * NB DB schema is 1 to many, but it is only possible to have one disqualification record per contact details
     *
     * @return null|Disqualification
     */
    public function getDisqualification()
    {
        if ($this->getDisqualifications()->isEmpty()) {
            return null;
        }

        return $this->getDisqualifications()->first();
    }

    /**
     * Get the disqualification status
     *
     * @return string Disqualification constant STATUS_NONE, STATUS_ACTIVE or STATUS_INACTIVE
     */
    public function getDisqualificationStatus()
    {
        if ($this->getDisqualification() === null) {
            return Disqualification::STATUS_NONE;
        }

        return $this->getDisqualification()->getStatus();
    }
}
