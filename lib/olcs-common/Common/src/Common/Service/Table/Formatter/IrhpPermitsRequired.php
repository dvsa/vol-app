<?php

namespace Common\Service\Table\Formatter;

use Common\RefData;
use Common\Util\Escape;

/**
 * IRHP Permit Type formatter
 */
class IrhpPermitsRequired implements FormatterPluginManagerInterface
{
    public const NAME = 'IrhpPermitsRequired';

    /**
     * Format
     *
     * Returns the number of IRHP permits required
     *
     * @param array $data
     *
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {
        $value = $data['permitsRequired'];

        $certificateTypes = [
            RefData::CERT_ROADWORTHINESS_TRAILER_PERMIT_TYPE_ID,
            RefData::CERT_ROADWORTHINESS_VEHICLE_PERMIT_TYPE_ID,
        ];

        if (in_array($data['typeId'], $certificateTypes)) {
            $value = 1;
        }

        return Escape::html($value);
    }
}
