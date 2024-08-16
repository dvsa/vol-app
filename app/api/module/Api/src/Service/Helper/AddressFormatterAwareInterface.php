<?php

namespace Dvsa\Olcs\Api\Service\Helper;

/**
 * Interface AddressFormatterAwareInterface
 * @package Dvsa\Olcs\Api\Service\Helper
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
interface AddressFormatterAwareInterface
{
    /**
     * @return FormatAddress
     */
    public function getAddressFormatter();

    /**
     * @return mixed
     */
    public function setAddressFormatter(FormatAddress $addressFormatter);
}
