<?php

/**
 * Sum Columns formatter
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Common\Service\Table\Formatter;

/**
 * Sum Columns formatter
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class SumColumns implements FormatterPluginManagerInterface
{
    /**
     * Sums the data of a specific columns
     *
     * @param  array $data
     * @param  array $column
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {
        $total = 0;
        if (isset($column['columns']) && is_array($column['columns'])) {
            foreach ($column['columns'] as $name) {
                if (!isset($data[$name])) {
                    continue;
                }
                if (!is_numeric($data[$name])) {
                    continue;
                }
                $total += (float)$data[$name];
            }
        }

        return (string)$total;
    }
}
