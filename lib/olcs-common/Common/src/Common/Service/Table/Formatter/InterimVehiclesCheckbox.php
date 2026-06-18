<?php

/**
 * Interim Vehicles Checkbox formatter
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Common\Service\Table\Formatter;

/**
 * Interim Vehicles Checkbox formatter
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class InterimVehiclesCheckbox implements FormatterPluginManagerInterface
{
    /**
     * Format a checkbox
     *
     * @param  array $data
     * @param  array $column
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {
        $format = '<input type="checkbox" value="' . $data['id'] . '" name="vehicles[id][]" %s>';
        if (
            isset($data['interimApplication'])
            && isset($data['interimApplication']['id'])
        ) {
            return sprintf($format, 'checked');
        }

        return sprintf($format, '');
    }
}
