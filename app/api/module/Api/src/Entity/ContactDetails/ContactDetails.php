<?php

namespace Dvsa\Olcs\Api\Entity\ContactDetails;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\ContactDetails\Address;
use Dvsa\Olcs\Api\Entity\Person\Person;

/**
 * ContactDetails Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="contact_details",
 *    indexes={
 *        @ORM\Index(name="ix_contact_details_person_id", columns={"person_id"}),
 *        @ORM\Index(name="ix_contact_details_address_id", columns={"address_id"}),
 *        @ORM\Index(name="ix_contact_details_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_contact_details_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_contact_details_contact_type", columns={"contact_type"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_contact_details_olbs_key_olbs_type", columns={"olbs_key","olbs_type"})
 *    }
 * )
 */
class ContactDetails extends AbstractContactDetails
{
    public const TRANSPORT_MANAGER_STATUS_CURRENT = 'tm_s_cur';
    public const TRANSPORT_MANAGER_STATUS_DISQUALIFIED = 'tm_s_dis';
    public const TRANSPORT_MANAGER_STATUS_REMOVED = 'tm_s_rem';

    public const TRANSPORT_MANAGER_TYPE_EXTERNAL = 'tm_t_e';
    public const TRANSPORT_MANAGER_TYPE_BOTH = 'tm_t_b';

    public const CONTACT_TYPE_REGISTERED_ADDRESS = 'ct_reg';
    public const CONTACT_TYPE_COMPLAINANT = 'ct_complainant';
    public const CONTACT_TYPE_ESTABLISHMENT_ADDRESS = 'ct_est';
    public const CONTACT_TYPE_CORRESPONDENCE_ADDRESS = 'ct_corr';
    public const CONTACT_TYPE_TRANSPORT_CONSULTANT = 'ct_tcon';
    public const CONTACT_TYPE_TRANSPORT_MANAGER = 'ct_tm';
    public const CONTACT_TYPE_WORKSHOP = 'ct_work';
    public const CONTACT_TYPE_IRFO_OPERATOR = 'ct_irfo_op';
    public const CONTACT_TYPE_PARTNER = 'ct_partner';
    public const CONTACT_TYPE_OBJECTOR = 'ct_obj';
    public const CONTACT_TYPE_STATEMENT_REQUESTOR = 'ct_requestor';
    public const CONTACT_TYPE_USER = 'ct_user';
    public const CONTACT_TYPE_HACKNEY = 'ct_hackney';

    /**
     * Construct
     *
     * @param RefData $contactType Contact type
     *
     * @return void
     */
    public function __construct(RefData $contactType)
    {
        parent::__construct();
        $this->setContactType($contactType);
    }

    /**
     * Create
     *
     * @param RefData $contactType   Contact type
     * @param array   $contactParams Array of data as defined by Dvsa\Olcs\Transfer\Command\Partial\ContactDetails
     *
     * @return ContactDetails
     */
    public static function create(RefData $contactType, array $contactParams)
    {
        $contactDetails = new self($contactType);
        $contactDetails->update($contactParams, true);

        return $contactDetails;
    }

    /**
     * Update
     *
     * @param array $contactParams Array of data as defined by Dvsa\Olcs\Transfer\Command\Partial\ContactDetails
     * @param bool $allowUpdatePerson Will be true for all new record creations and updates, only false for edits from self-serve
     *
     * @return $this
     */
    public function update(array $contactParams, $allowUpdatePerson = true)
    {
        // each type may have different update
        match ($this->getContactType()->getId()) {
            self::CONTACT_TYPE_IRFO_OPERATOR => $this->updateIrfoOperator($contactParams),
            self::CONTACT_TYPE_PARTNER => $this->updatePartner($contactParams),
            self::CONTACT_TYPE_OBJECTOR => $this->updateObjector($contactParams),
            self::CONTACT_TYPE_STATEMENT_REQUESTOR => $this->updateStatementRequestor($contactParams),
            self::CONTACT_TYPE_TRANSPORT_MANAGER, self::CONTACT_TYPE_USER => $this->updateUser($contactParams, $allowUpdatePerson),
            self::CONTACT_TYPE_COMPLAINANT => $this->updateComplainant($contactParams),
            self::CONTACT_TYPE_CORRESPONDENCE_ADDRESS => $this->updateCorrespondenceAddress($contactParams),
            default => $this,
        };

        return $this;
    }

    /**
     * Update IRFO operator
     *
     * @param array $contactParams Array of data as defined by Dvsa\Olcs\Transfer\Command\Partial\ContactDetails
     *
     * @return void
     */
    private function updateIrfoOperator(array $contactParams)
    {
        if (isset($contactParams['emailAddress'])) {
            // set email address
            $this->setEmailAddress($contactParams['emailAddress']);
        }

        if (isset($contactParams['address'])) {
            // populate address
            $this->populateAddress($contactParams['address']);
        }

        if (isset($contactParams['phoneContacts'])) {
            // populate phone contacts
            $this->populatePhoneContacts($contactParams['phoneContacts']);
        }
    }

    /**
     * Update objector
     *
     * @param array $contactParams Array of data as defined by Dvsa\Olcs\Transfer\Command\Partial\ContactDetails
     *
     * @return void
     */
    private function updateObjector(array $contactParams)
    {
        if (isset($contactParams['emailAddress'])) {
            // set email address
            $this->setEmailAddress($contactParams['emailAddress']);
        }

        if (isset($contactParams['description'])) {
            // set description
            $this->setDescription($contactParams['description']);
        }

        if (isset($contactParams['address'])) {
            // populate address
            $this->populateAddress($contactParams['address']);
        }

        if (isset($contactParams['person'])) {
            // populate person
            $this->populatePerson($contactParams['person']);
        }

        if (isset($contactParams['phoneContacts'])) {
            // populate phone contacts
            $this->populatePhoneContacts($contactParams['phoneContacts']);
        }
    }


    /**
     * Update statement requestor
     *
     * @param array $contactParams Array of data as defined by Dvsa\Olcs\Transfer\Command\Partial\ContactDetails
     *
     * @return void
     */
    private function updateStatementRequestor(array $contactParams)
    {
        // populate address
        $this->populateAddress($contactParams['address']);

        // populate person
        $this->populatePerson($contactParams['person']);
    }

    /**
     * Update partner
     *
     * @param array $contactParams Array of data as defined by Dvsa\Olcs\Transfer\Command\User\UpdatePartner
     *
     * @return void
    */
    private function updatePartner(array $contactParams)
    {
        // set description
        $this->setDescription($contactParams['description']);

        // populate address
        $this->populateAddress($contactParams['address']);
    }

    /**
     * Update user
     *
     * @param array $contactParams Array of data as defined by Dvsa\Olcs\Transfer\Command\Partial\ContactDetails
     * @param bool $allowUpdatePerson Should person details be updated
     *
     * @return void
     */
    private function updateUser(array $contactParams, $allowUpdatePerson)
    {
        // set email address
        $this->setEmailAddress($contactParams['emailAddress']);

        // populate person if initiated by a caseworker
        if (isset($contactParams['person']) && $allowUpdatePerson) {
            $this->populatePerson($contactParams['person']);
        }

        if (isset($contactParams['address'])) {
            // populate address
            $this->populateAddress($contactParams['address']);
        }

        if (isset($contactParams['phoneContacts'])) {
            // populate phone contacts
            $this->populatePhoneContacts($contactParams['phoneContacts']);
        }
    }

    /**
     * Update complainant
     *
     * @param array $contactParams Array of data as defined by Dvsa\Olcs\Transfer\Command\Partial\ContactDetails
     *
     * @return void
     */
    private function updateComplainant(array $contactParams)
    {
        // populate address
        $this->populateAddress($contactParams['address']);

        // populate person
        $this->populatePerson($contactParams['person']);
    }

    /**
     * Update correspondence address
     *
     * @param array $contactParams Array of data as defined by Dvsa\Olcs\Transfer\Command\Partial\OperatorContactDetails
     *
     * @return void
     */
    private function updateCorrespondenceAddress(array $contactParams)
    {
        if (isset($contactParams['emailAddress'])) {
            // set email address
            $this->setEmailAddress($contactParams['emailAddress']);
        }

        if (isset($contactParams['address'])) {
            // populate address
            $this->populateAddress($contactParams['address']);
        }

        if (isset($contactParams['phoneContacts'])) {
            // populate phone contacts
            $this->populatePhoneContacts($contactParams['phoneContacts']);
        }
    }

    /**
     * Create address object
     *
     * @param array $addressParams Array of data as defined by Dvsa\Olcs\Transfer\Command\Partial\Address
     *
     * @return void
     */
    private function populateAddress(array $addressParams)
    {
        if (!($this->address instanceof Address)) {
            $this->address = new Address();
        }

        $this->address->updateAddress(
            $addressParams['addressLine1'],
            $addressParams['addressLine2'],
            $addressParams['addressLine3'],
            $addressParams['addressLine4'],
            $addressParams['town'],
            $addressParams['postcode'],
            !empty($addressParams['countryCode']) ? $addressParams['countryCode'] : null
        );
    }

    /**
     * Populate phone contacts
     *
     * @param array $phoneContacts List of Dvsa\Olcs\Transfer\Command\Partial\PhoneContact
     *
     * @return void
     */
    private function populatePhoneContacts(array $phoneContacts)
    {
        $seen = [];

        $collection = $this->getPhoneContacts()->toArray();

        foreach ($phoneContacts as $phoneContact) {
            if (empty($phoneContact['phoneNumber'])) {
                // filter out empty values
                continue;
            }

            if (isset($phoneContact['id']) && !empty($collection[$phoneContact['id']])) {
                // update
                $phoneContactEntity = $collection[$phoneContact['id']];
                $phoneContactEntity->setPhoneNumber($phoneContact['phoneNumber']);

                $seen[$phoneContact['id']] = $phoneContact['id'];
            } else {
                // create
                $phoneContactEntity = new PhoneContact($phoneContact['phoneContactType']);
                $phoneContactEntity->setContactDetails($this);
                $phoneContactEntity->setPhoneNumber($phoneContact['phoneNumber']);

                $this->phoneContacts->add($phoneContactEntity);
            }
        }

        // remove the rest
        foreach (array_diff_key($collection, $seen) as $key => $entity) {
            // unlink
            $this->phoneContacts->remove($key);
        }
    }

    /**
     * Populate person
     *
     * @param array $personParams Array of data as defined by Dvsa\Olcs\Transfer\Command\Partial\Person
     *
     * @return void
     */
    private function populatePerson(array $personParams)
    {
        if (!($this->person instanceof Person)) {
            $this->person = new Person();
        }

        $this->person->updatePerson(
            $personParams['forename'],
            $personParams['familyName'],
            $this->getDefaultParameter($personParams, 'title'),
            $this->getDefaultParameter($personParams, 'birthDate')
        );
    }

    /**
     * Generates a parameter from either the params array, or returns a default.
     *
     * @param array  $params  Params
     * @param string $var     Variable name
     * @param mixed  $default Default value
     *
     * @return mixed
     */
    private function getDefaultParameter($params, $var, mixed $default = null)
    {
        return $params[$var] ?? $default;
    }

    /**
     * Update contact details with person and email address
     *
     * @param Person|null $person       Person
     * @param string|null $emailAddress Email address
     *
     * @return void
     */
    public function updateContactDetailsWithPersonAndEmailAddress($person = null, $emailAddress = null)
    {
        if ($person !== null) {
            $this->setPerson($person);
        }
        $this->setEmailAddress($emailAddress);
    }

    /**
     * Get phone contact number
     *
     * @param string $type type
     *
     * @return null
     */
    public function getPhoneContactNumber($type)
    {
        $phoneContacts = $this->getPhoneContacts();
        foreach ($phoneContacts as $pc) {
            if ($pc->getPhoneContactType()->getId() === $type) {
                return $pc->getPhoneNumber();
            }
        }
        return null;
    }
}
