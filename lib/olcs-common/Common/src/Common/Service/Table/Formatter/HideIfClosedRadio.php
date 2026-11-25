<?php

/**
 * Hide If Closed Radio formatter
 */

namespace Common\Service\Table\Formatter;

use Common\Util\Escape;

/**
 * Hide If Closed Radio formatter
 */
class HideIfClosedRadio implements FormatterPluginManagerInterface
{
    /**
     * Format a radio
     *
     * @param  array $data
     * @param  array $column
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {
        if (!empty($data['closedDate'])) {
            return '';
        }

        $id = $column['selectionId'] ?? 'submission-' . $data['id'];
        return '<input type="radio" id="' . Escape::htmlAttr($id) . '" value="'
            . Escape::htmlAttr($data['id']) . '" name="id">'
            . '<label class="govuk-visually-hidden" for="' . Escape::htmlAttr($id) . '">Select submission '
            . Escape::html($data['id']) . '</label>';
    }
}
