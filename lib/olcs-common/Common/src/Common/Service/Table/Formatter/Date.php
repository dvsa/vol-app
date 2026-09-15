<?php

/**
 * Date formatter
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Service\Table\Formatter;

/**
 * Date formatter
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Date implements FormatterPluginManagerInterface
{
    /**
     * Format a date
     *
     * @param  array $data
     * @param  array $column
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {
        if (!isset($column['dateformat'])) {
            $column['dateformat'] = 'd/m/Y';
        }

        if (isset($data[$column['name']]) && !is_null($data[$column['name']])) {
            $date = $data[$column['name']];

            if (is_array($date) && isset($date['date'])) {
                $date = $date['date'];
            }

            return date($column['dateformat'], strtotime($date));
        }

        return '';
    }
}
