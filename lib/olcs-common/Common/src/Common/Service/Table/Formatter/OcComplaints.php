<?php

/**
 * OcComplaints.php
 */

namespace Common\Service\Table\Formatter;

/**
 * Class OcComplaints
 *
 * Format results for the table.
 *
 * @package Common\Service\Table\Formatter
 *
 * @author Joshua Curtis <josh.curtis@valtech.co.uk>
 */
class OcComplaints implements FormatterPluginManagerInterface
{
    /**
     * Get the complaints for the operating centre and return a count.
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

        return empty($data['operatingCentre']['complaints']) ? 0 : count($data['operatingCentre']['complaints']);
    }
}
