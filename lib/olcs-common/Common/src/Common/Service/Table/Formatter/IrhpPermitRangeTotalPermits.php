<?php

namespace Common\Service\Table\Formatter;

/**
 * IRHP Permit Range table - Total Permits column formatter
 *
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */
class IrhpPermitRangeTotalPermits implements FormatterPluginManagerInterface
{
    /**
     * Format
     *
     * Returns a formatted column for the Total Permits
     *
     * @param array $data
     */
    #[\Override]
    public function format($data, $column = []): int
    {
        // Need to add one to get a count of all the permits inclusive
        // E.g. Permits 1 to 16 = 16 total permits.
        return ((int) $data['toNo'] - (int) $data['fromNo']) + 1;
    }
}
