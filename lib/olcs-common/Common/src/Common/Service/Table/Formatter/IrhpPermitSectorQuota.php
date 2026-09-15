<?php

namespace Common\Service\Table\Formatter;

use Common\Util\Escape;

/**
 * IRHP Permit Sector table - Quota Number column formatter
 *
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */
class IrhpPermitSectorQuota implements FormatterPluginManagerInterface
{
    /**
     * Format
     *
     * Returns an editable Sector Quota Number
     *
     * @param array $data
     *
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {
        $quotaNumber = $data['quotaNumber'] ? Escape::html($data['quotaNumber']) : 0;
        $id = Escape::html($data['id']);

        return sprintf(
            "<input type='number' value='%s' name='sectors[%s]' />",
            $quotaNumber,
            $id
        );
    }
}
