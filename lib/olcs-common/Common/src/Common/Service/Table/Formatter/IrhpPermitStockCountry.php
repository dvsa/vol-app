<?php

namespace Common\Service\Table\Formatter;

use Common\Util\Escape;

/**
 * IRHP Permit Stock table - Country column formatter
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class IrhpPermitStockCountry implements FormatterPluginManagerInterface
{
    /**
     * Returns the country name if applicable, along with the permit category if applicable
     *
     * @param array $data
     * @param array $column
     *
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {
        $value = 'N/A';

        if (isset($data['country'])) {
            $value = $data['country']['countryDesc'];

            if (isset($data['permitCategory'])) {
                $value .= ' ' . $data['permitCategory']['description'];
            }
        }

        return Escape::html($value);
    }
}
