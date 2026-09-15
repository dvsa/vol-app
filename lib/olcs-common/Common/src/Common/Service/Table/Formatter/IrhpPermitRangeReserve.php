<?php

namespace Common\Service\Table\Formatter;

/**
 * IRHP Permit Range table - Minister of State Reserve column formatter
 *
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */
class IrhpPermitRangeReserve implements FormatterPluginManagerInterface
{
    /**
     * Format
     *
     * Returns a formatted column for the State Reserve
     *
     * @param array $data
     * @param array $column
     *
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {
        return $data['ssReserve'] ? 'Yes' : 'N/A';
    }
}
