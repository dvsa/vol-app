<?php

namespace Dvsa\Olcs\Api\Service\Nr\Filter;

use Laminas\Filter\AbstractFilter as AbstractFilter;

/**
 * Class LicenceNumber
 * @package Dvsa\Olcs\Api\Service\Nr\Filter
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 * @template-extends AbstractFilter<array>
 */
class LicenceNumber extends AbstractFilter
{
    /**
     * Returns the result of filtering $value
     * We expect either a community licence e.g. UKGB/OB1234567/00000 or a standard licence e.g. OB1234567
     *
     * @param array $value input value
     *
     * @return array
     */
    public function filter($value)
    {
        //licence number defaults to the the initial value
        $value['licenceNumber'] = $value['communityLicenceNumber'];

        //see if we can split into three parts, as per a community licence number
        $explodedCommunityLicence = explode('/', (string) $value['communityLicenceNumber']);

        //if we have three parts, assume a community licence and extract the middle part
        if (count($explodedCommunityLicence) === 3) {
            $value['licenceNumber'] = $explodedCommunityLicence[1];
        }

        return $value;
    }
}
