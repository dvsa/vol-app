<?php

/**
 * OcConditions.php
 */

namespace Common\Service\Table\Formatter;

use Common\RefData;

/**
 * Class OcConditions
 *
 * Format results for the table.
 *
 * @package Common\Service\Table\Formatter
 *
 * @author Joshua Curtis <josh.curtis@valtech.co.uk>
 */
class OcConditions implements FormatterPluginManagerInterface
{
    /**
     * Get the conditions for the operating centre and return a count.
     *
     * @param array $data   The row data.
     * @param array $column The column data.
     *
     * @return mixed
     */
    #[\Override]
    public function format($data, $column = [])
    {
        unset($column);

        $count = 0;

        if (!is_null($data['conditions'])) {
            foreach ($data['conditions'] as $condition) {
                if (is_null($condition['licence'])) {
                    continue;
                }
                if ($condition['conditionType']['id'] !== RefData::TYPE_CONDITION) {
                    continue;
                }
                ++$count;
            }
        }

        return $count;
    }
}
