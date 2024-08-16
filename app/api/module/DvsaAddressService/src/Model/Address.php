<?php

namespace Dvsa\Olcs\DvsaAddressService\Model;

class Address implements \JsonSerializable
{
    public function __construct(
        protected ?string $addressLine1,
        protected ?string $addressLine2,
        protected ?string $addressLine3,
        protected ?string $addressLine4,
        protected ?string $postTown,
        protected ?string $postcode,
        protected ?string $postcodeTrim,
        protected ?string $organisationName,
        protected ?string $uprn,
        protected ?string $administrativeArea
    ) {
    }

    public function getAddressLine1(): ?string
    {
        return $this->addressLine1;
    }

    public function getAddressLine2(): ?string
    {
        return $this->addressLine2;
    }

    public function getAddressLine3(): ?string
    {
        return $this->addressLine3;
    }

    public function getAddressLine4(): ?string
    {
        return $this->addressLine4;
    }

    public function getPostTown(): ?string
    {
        return $this->postTown;
    }

    public function getPostcode(): ?string
    {
        return $this->postcode;
    }

    public function getPostcodeTrim(): ?string
    {
        return $this->postcodeTrim;
    }

    public function getOrganisationName(): ?string
    {
        return $this->organisationName;
    }

    public function getUprn(): ?string
    {
        return $this->uprn;
    }

    /**
     * @deprecated
     */
    public function getAdministrativeArea(): ?string
    {
        return $this->administrativeArea;
    }

    public function jsonSerialize(): array
    {
        return [
            'address_line1' => $this->addressLine1,
            'address_line2' => $this->addressLine2,
            'address_line3' => $this->addressLine3,
            'address_line4' => $this->addressLine4,
            'post_town' => $this->postTown,
            'postcode' => $this->postcode,
            'postcode_trim' => $this->postcodeTrim,
            'organisation_name' => $this->organisationName,
            'uprn' => $this->uprn,
            'administrative_area' => $this->administrativeArea,
        ];
    }

    public function toArray(): array
    {
        return $this->jsonSerialize();
    }
}
