<?php

namespace Dvsa\Olcs\Api\Service\Helper;

/**
 * Class AddressFormatterAwareTrait
 * @package Dvsa\Olcs\Api\Service\Helper
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
trait AddressFormatterAwareTrait
{
    /**
     * @var FormatAddress
     */
    private $addressFormatter;

    /**
     * @return FormatAddress
     */
    public function getAddressFormatter()
    {
        return $this->addressFormatter;
    }

    public function setAddressFormatter(FormatAddress $addressFormatter)
    {
        $this->addressFormatter = $addressFormatter;
    }
}
