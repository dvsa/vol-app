<?php

namespace Dvsa\Olcs\DvsaAddressService\Service;

use Dvsa\Olcs\DvsaAddressService\Model\Address;

interface AddressInterface
{
    /**
     * @return Address[]
     */
    public function lookupAddress(string $query): array;
}
