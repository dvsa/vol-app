<?php

/**
 * CommunityLicenceIssueNo formatter
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Service\Table\Formatter;

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
        return str_pad($data[$column['name']], 5, '0', STR_PAD_LEFT) .
            ($data[$column['name']] === 0 ? ' (Office copy)' : '');
    }
}
