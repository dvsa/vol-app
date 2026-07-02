<?php

namespace Common\Service\Table\Formatter;

use Common\Util\Escape;

/**
 * IRHP Permit Range table - Restricted Countries column formatter
 *
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */
class IrhpPermitRangeRestrictedCountries implements FormatterPluginManagerInterface
{
    /**
     * Format
     *
     * Returns a formatted column for the Restricted Countries
     *
     * @param array $data
     * @param array $column
     *
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {
        $countryNames = [];

        foreach ($data['countrys'] as $country) {
            $countryNames[] = Escape::html($country['countryDesc']);
        }

        sort($countryNames, SORT_STRING);

        return implode(', ', $countryNames);
    }
}
