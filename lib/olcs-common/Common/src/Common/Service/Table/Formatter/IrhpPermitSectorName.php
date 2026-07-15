<?php

namespace Common\Service\Table\Formatter;

use Common\Util\Escape;

/**
 * IRHP Permit Sector table - Sector Name column formatter
 *
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */
class IrhpPermitSectorName implements FormatterPluginManagerInterface
{
    /**
     * Format
     *
     * Returns the Sector Name
     *
     * @param array $data
     *
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {
        if (empty($data['sector']['description'])) {
            return Escape::html($data['sector']['name']);
        }

        return Escape::html($data['sector']['name']) . ": " . Escape::html($data['sector']['description']);
    }
}
