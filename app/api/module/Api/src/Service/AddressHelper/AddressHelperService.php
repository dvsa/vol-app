<?php

namespace Dvsa\Olcs\Api\Service\AddressHelper;

use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea;
use Dvsa\Olcs\DvsaAddressService\Model\Address;
use Dvsa\Olcs\DvsaAddressService\Service\AddressInterface;

class AddressHelperService
{
    public function __construct(
        protected AddressInterface $addressService,
        protected Repository\PostcodeEnforcementArea $postcodeEnforcementAreaRepository,
        protected Repository\AdminAreaTrafficArea $adminAreaTrafficAreaRepository
    ) {
    }

    /**
     * Lookup Address.
     *
     * @param string|int $query Postcode or UPRN
     *
     * @return Address[]
     */
    public function lookupAddress(string|int $query): array
    {
        $query = (string) $query;

        return $this->addressService->lookupAddress($query);
    }

    /**
     * Fetch Traffic Area by Postcode or UPRN.
     *
     * If queried using Postcode and multiple addresses are found, the first address is used.
     *
     * @param string|int $query Postcode or UPRN
     * @return TrafficArea|null
     */
    public function fetchTrafficAreaByPostcodeOrUprn(string|int $query): ?Entity\TrafficArea\TrafficArea
    {
        $addressData = $this->lookupAddress($query);
        if (empty($addressData)) {
            return null;
        }

        $addressData = $addressData[0];

        $adminArea = $addressData->getAdministrativeArea();
        if (empty($adminArea)) {
            return null;
        }

        try {
            return $this->adminAreaTrafficAreaRepository->fetchById($adminArea)->getTrafficArea();
        } catch (NotFoundException) {
            return null;
        }
    }

    public function fetchEnforcementAreaByPostcode(string $postcode): ?Entity\EnforcementArea\EnforcementArea
    {
        /**
         * This postcode pattern ensures that:
         *  - The space between the prefix and the suffix is optional.
         *  - It accurately captures the prefix and the first digit of the suffix.
         *  - It matches the entire postal code structure, reducing the risk of partial or incorrect matches that
         *    could lead to unexpected behavior or vulnerabilities.
         */
        preg_match('/^([A-Za-z]{1,2}\d{1,2})\s?(\d)[A-Za-z]{2}$/', $postcode, $matches);

        if (empty($matches)) {
            return null;
        }

        $prefix = $matches[1];
        $suffixDigit = $matches[2];

        $pea = $this->postcodeEnforcementAreaRepository->fetchByPostcodeId($prefix . ' ' . $suffixDigit);

        if ($pea === null) {
            // if not found, try by just the prefix
            $pea = $this->postcodeEnforcementAreaRepository->fetchByPostcodeId($prefix);
        }

        return $pea?->getEnforcementArea();
    }
}
