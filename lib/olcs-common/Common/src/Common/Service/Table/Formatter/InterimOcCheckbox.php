<?php

/**
 * Interim Operating Centres Checkbox formatter
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Common\Service\Table\Formatter;

use Common\Util\Escape;

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
        $id = $column['selectionId'] ?? 'interim-operating-centre-' . $data['id'];
        $address = $data['operatingCentre']['address'];
        $description = implode(', ', array_filter(
            [$address['addressLine1'] ?? '', $address['town'] ?? ''],
            static fn($part) => $part !== '' && $part !== null
        ));
        $format = '<input type="checkbox" id="' . Escape::htmlAttr($id) . '" value="'
            . Escape::htmlAttr($data['id']) . '" name="operatingCentres[id][]" %s>'
            . '<label class="govuk-visually-hidden" for="' . Escape::htmlAttr($id) . '">Select operating centre '
            . Escape::html($description) . '</label>';
        if (
            isset($data['isInterim'])
            && $data['isInterim'] == 'Y'
        ) {
            return sprintf($format, 'checked');
        }

        return sprintf($format, '');
    }
}
