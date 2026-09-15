<?php

namespace Common\Service\Table\Formatter;

use Common\Util\Escape;

/**
 * IRHP Permit Jurisdiction Table - Traffic Area column formatter
 *
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */
class IrhpPermitJurisdictionTrafficArea implements FormatterPluginManagerInterface
{
    /**
     * Format
     *
     * Returns the Traffic Area
     *
     * @param array $data
     *
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {
        return Escape::html($data['trafficArea']['name']);
    }
}
