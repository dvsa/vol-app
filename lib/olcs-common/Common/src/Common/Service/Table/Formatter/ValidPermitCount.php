<?php

/**
 * Valid permit count formatter
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */

namespace Common\Service\Table\Formatter;

use Common\RefData;

/**
 * Valid permit count formatter
 */
class ValidPermitCount implements FormatterPluginManagerInterface
{
    /**
     * Valid permit count
     *
     * @param array $row    Row data
     * @param array $column Column data
     *
     * @return string
     */
    #[\Override]
    public function format($row, $column = null)
    {
        $countOverrideTypeIds = [
            RefData::CERT_ROADWORTHINESS_VEHICLE_PERMIT_TYPE_ID,
            RefData::CERT_ROADWORTHINESS_TRAILER_PERMIT_TYPE_ID,
        ];

        $permitTypeId = $row['typeId'];

        if (in_array($permitTypeId, $countOverrideTypeIds)) {
            return 1;
        }

        return $row['validPermitCount'];
    }
}
