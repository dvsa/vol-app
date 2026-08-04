<?php

/**
 * Hide If Closed Radio formatter
 */

namespace Common\Service\Table\Formatter;

/**
 * Hide If Closed Radio formatter
 */
class HideIfClosedRadio implements FormatterPluginManagerInterface
{
    /**
     * Format a radio
     *
     * @param  array $data
     * @param  array $column
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {
        if (!empty($data['closedDate'])) {
            return '';
        }

        return '<input type="radio" value="' . $data['id'] . '" name="id">';
    }
}
