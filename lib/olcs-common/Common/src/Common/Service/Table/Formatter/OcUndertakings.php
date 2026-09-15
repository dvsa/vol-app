<?php

/**
 * OcUndertakings.php
 */

namespace Common\Service\Table\Formatter;

use Common\RefData;

/**
 * Class OcUndertakings
 *
 * Format results for the table.
 *
 * @package Common\Service\Table\Formatter
 *
 * @author Joshua Curtis <josh.curtis@valtech.co.uk>
 */
class OcUndertakings implements FormatterPluginManagerInterface
{
    /**
     * Get the undertakings for the operating centre and return a count.
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

        if (!is_null($data['undertakings'])) {
            foreach ($data['undertakings'] as $undertaking) {
                if (is_null($undertaking['licence'])) {
                    continue;
                }
                if ($undertaking['conditionType']['id'] !== RefData::TYPE_UNDERTAKING) {
                    continue;
                }
                ++$count;
            }
        }

        return $count;
    }
}
