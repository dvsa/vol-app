<?php

/**
 * Date and time formatter
 *
 * @author Jessica Rowbottom <jess.rowbottom@valtech.co.uk>
 */

namespace Common\Service\Table\Formatter;

use Common\Module;

/**
 * Date and time formatter
 *
 * @author Jessica Rowbottom <jess.rowbottom@valtech.co.uk>
 */
class DateTime implements FormatterPluginManagerInterface
{
    /**
     * Format a date and time
     *
     * @param  array $data
     * @param  array $column
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {
        if (!isset($column['dateformat'])) {
            $column['dateformat'] = Module::$dateTimeFormat;
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
