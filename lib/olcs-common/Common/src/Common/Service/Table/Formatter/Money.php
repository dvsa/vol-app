<?php

namespace Common\Service\Table\Formatter;

/**
 * Money formatter
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class Money implements FormatterPluginManagerInterface
{
    /**
     * Format a fee amount
     *
     * @param  array $data
     * @param  array $column
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {
        if (isset($column['name']) && isset($data[$column['name']])) {
            $amount = $data[$column['name']];
            return '£' . number_format($amount, 2);
        }

        return '';
    }
}
