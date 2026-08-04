<?php

namespace Common\Service\Table\Formatter;

use Common\Util\Escape;

/**
 * IRHP Permit Organisation Name formatter
 */
class IrhpPermitOrganisationName implements FormatterPluginManagerInterface
{
    /**
     * Format
     *
     * Returns the IRHP Permit Organisation Name
     *
     * @param array $data
     *
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {
        if (!isset($data['irhpPermitApplication']['relatedApplication']['licence']['organisation']['name'])) {
            return null;
        }

        $value = $data['irhpPermitApplication']['relatedApplication']['licence']['organisation']['name'];
        return Escape::html($value);
    }
}
