<?php

namespace Common\Service\Table\Formatter;

/**
 * IRHP Permit Range table - eplacement Stock column formatter
 *
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */
class IrhpPermitRangeReplacement implements FormatterPluginManagerInterface
{
    /**
     * Format
     *
     * Returns a formatted column for the Replacement Stock
     *
     * @param array $data
     * @param array $column
     *
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {
        return $data['lostReplacement'] ? 'Yes' : 'N/A';
    }
}
