<?php

/**
 * CommunityLicenceIssueNo formatter
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Service\Table\Formatter;

use Common\Util\Escape;

/**
 * CommunityLicenceIssueNo formatter
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CommunityLicenceIssueNo implements FormatterPluginManagerInterface
{
    /**
     * Format the issue no field
     *
     * @param  array $data
     * @param  array $column
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {
        return Escape::html(str_pad($data[$column['name']], 5, '0', STR_PAD_LEFT)) .
            ($data[$column['name']] === 0 ? ' (Office copy)' : '');
    }
}
