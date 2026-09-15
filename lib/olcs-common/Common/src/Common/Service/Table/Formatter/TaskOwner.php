<?php

/**
 * Task Owner Formatter
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Service\Table\Formatter;

use Common\Util\Escape;

/**
 * Task Owner Formatter
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TaskOwner implements FormatterPluginManagerInterface
{
    /**
     * Format a task owner
     *
     * @param  array $data
     * @param  array $column
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {
        $owner = '';

        if (!empty($data['teamName'])) {
            $owner = Escape::html($data['teamName']) . ' ';
        }

        // trim leading/trailing spaces and commas
        $data['ownerName'] = trim($data['ownerName'], ' ,');

        $user = empty($data['ownerName']) ? 'Unassigned' : Escape::html($data['ownerName']);

        return $owner . '(' . $user . ')';
    }
}
