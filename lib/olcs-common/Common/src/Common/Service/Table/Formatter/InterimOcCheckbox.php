<?php

/**
 * Interim Operating Centres Checkbox formatter
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Common\Service\Table\Formatter;

/**
 * Interim Operating Centres Checkbox formatter
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class InterimOcCheckbox implements FormatterPluginManagerInterface
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
        $format = '<input type="checkbox" value="' . $data['id'] . '" name="operatingCentres[id][]" %s>';
        if (
            isset($data['isInterim'])
            && $data['isInterim'] == 'Y'
        ) {
            return sprintf($format, 'checked');
        }

        return sprintf($format, '');
    }
}
