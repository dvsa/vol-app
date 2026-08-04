<?php

namespace Dvsa\Olcs\Transfer\Command;

use Dvsa\Olcs\Transfer\FieldType\Traits;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * Update Addresses
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 *
 * @TODO: apologies; there is some validation missing off this
 * command. Correspondence, contact, establishment and consultant
 * are all arrays of data which will need partials creating in order
 * to validate them.
 *
 * I'd sort this myself but won't have time before leaving. Consider
 * this my parting gift to one unfortunate developer of the future...
 */
abstract class AbstractUpdateAddresses extends AbstractCommand
{
    use Traits\Identity;

    /**
     * @Transfer\Optional
     */
    protected $correspondence;

    /**
     * @Transfer\Optional
     */
    protected $contact;

    /**
     * @Transfer\Partial("Dvsa\Olcs\Transfer\Command\Partial\AddressOptional")
     * @Transfer\Optional
     */
    protected $correspondenceAddress;

    /**
     * @Transfer\Optional
     */
    protected $establishment;

    /**
     * @Transfer\Partial("Dvsa\Olcs\Transfer\Command\Partial\AddressOptional")
     * @Transfer\Optional
     */
    protected $establishmentAddress;

    /**
     * @Transfer\Optional
     */
    protected $consultant;

    /**
     * Get Correspondence
     *
     * @return array|null
     */
    public function getCorrespondence()
    {
        return $this->correspondence;
    }

    /**
     * Get Contact
     *
     * @return array|null
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * Get Corr Address
     *
     * @return array|null
     */
    public function getCorrespondenceAddress()
    {
        return $this->correspondenceAddress;
    }

    /**
     * Get Establishment
     *
     * @return array|null
     */
    public function getEstablishment()
    {
        return $this->establishment;
    }

    /**
     * Get Establishment Address
     *
     * @return array|null
     */
    public function getEstablishmentAddress()
    {
        return $this->establishmentAddress;
    }

    /**
     * Get Consultant
     *
     * @return array|null
     */
    public function getConsultant()
    {
        return $this->consultant;
    }
}
