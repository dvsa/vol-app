<?php

namespace Dvsa\Olcs\Api\Entity\ContactDetails;

use Doctrine\ORM\Mapping as ORM;

/**
 * Address Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="address",
 *    indexes={
 *        @ORM\Index(name="ix_address_country_code", columns={"country_code"}),
 *        @ORM\Index(name="ix_address_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_address_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_address_admin_area", columns={"admin_area"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_address_olbs_key_olbs_type", columns={"olbs_key","olbs_type"})
 *    }
 * )
 */
class Address extends AbstractAddress
{
    public const CONTACT_TYPE_REGISTERED_ADDRESS = 'ct_reg';

    /**
     * Update address
     *
     * @param string  $addressLine1 Address line 1
     * @param string  $addressLine2 Address line 2
     * @param string  $addressLine3 Address line 3
     * @param string  $addressLine4 Address line 4
     * @param string  $town         Town
     * @param string  $postcode     Postcode
     * @param Country $countryCode  Country code
     *
     * @return void
     */
    public function updateAddress(
        $addressLine1 = null,
        $addressLine2 = null,
        $addressLine3 = null,
        $addressLine4 = null,
        $town = null,
        $postcode = null,
        Country $countryCode = null
    ) {
        $this->setAddressLine1($addressLine1);
        $this->setAddressLine2($addressLine2);
        $this->setAddressLine3($addressLine3);
        $this->setAddressLine4($addressLine4);
        $this->setTown($town);
        $this->setPostcode($postcode);
        $this->setCountryCode($countryCode);
    }

    /**
     * To array
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'addressLine1' => $this->getAddressLine1(),
            'addressLine2' => $this->getAddressLine2(),
            'addressLine3' => $this->getAddressLine3(),
            'addressLine4' => $this->getAddressLine4(),
            'town' => $this->getTown(),
            'postcode' => $this->getPostcode(),
            'countryCode' => $this->getCountryCode() ? $this->getCountryCode()->getId() : null,
        ];
    }

    /**
     * Is empty address
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty(
            $this->getAddressLine1() . $this->getAddressLine2() . $this->getAddressLine3() . $this->getAddressLine4() .
            $this->getTown() . $this->getPostcode()
        );
    }
}
