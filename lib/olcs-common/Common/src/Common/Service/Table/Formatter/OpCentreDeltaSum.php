<?php

/**
 * OpCenterDeltaSum.php
 */

namespace Common\Service\Table\Formatter;

/**
 * Class OpCenterDeltaSum
 *
 * Work out the sum of all the values in a column, this formatter deals with domain
 * specific delta records.
 *
 * @package Common\Service\Table\Formatter
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
class OpCentreDeltaSum implements FormatterPluginManagerInterface
{
    /**
     * Calculate the total from all records including the deltas.
     *
     * @param array $data   The data from the table.
     * @param array $column The column data.
     *
     * @return string The total count.
     */
    #[\Override]
    public function format($data, $column = [])
    {
        $sum = 0;

        if (isset($column['name'])) {
            foreach ($data as $operatingCenter) {
                switch ($operatingCenter['action']) {
                    case 'U':
                    case 'E':
                    case 'A':
                        $sum += $operatingCenter[$column['name']];
                        break;
                    default:
                        break;
                }
            }
        }

        return (string)$sum;
    }
}
