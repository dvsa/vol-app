<?php

/**
 * Interim Vehicles Checkbox formatter
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Common\Service\Table\Formatter;

use Common\Util\Escape;

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
        $id = $column['selectionId'] ?? 'interim-vehicle-' . $data['id'];
        $format = '<input type="checkbox" id="' . Escape::htmlAttr($id) . '" value="'
            . Escape::htmlAttr($data['id']) . '" name="vehicles[id][]" %s>'
            . '<label class="govuk-visually-hidden" for="' . Escape::htmlAttr($id) . '">Select vehicle '
            . Escape::html($data['vehicle']['vrm']) . '</label>';
        if (
            isset($data['interimApplication'])
            && isset($data['interimApplication']['id'])
        ) {
            return sprintf($format, 'checked');
        }

        return sprintf($format, '');
    }
}
