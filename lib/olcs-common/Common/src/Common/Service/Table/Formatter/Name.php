<?php

/**
 * Name formatter
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */

namespace Common\Service\Table\Formatter;

use Common\Service\Helper\DataHelperService;

/**
 * Name formatter
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class Name implements FormatterPluginManagerInterface
{
    public function __construct(private DataHelperService $dataHelper)
    {
    }

    /**
     * Format a name
     *
     * @param  array $data   data row
     * @param  array $column column specification
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {
        // if column[name] is specified, look within that index for the data
        if (isset($column['name'])) {
            // if column[name] contains "->" look deeper
            if (strpos($column['name'], '->')) {
                $data = $this->dataHelper->fetchNestedData($data, $column['name']);
            } elseif (isset($data[$column['name']])) {
                $data = $data[$column['name']];
            }
        }

        $title = empty($data['title']['description']) ? '' : $data['title']['description'] . ' ';
        return htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . htmlspecialchars($data['forename'], ENT_QUOTES, 'UTF-8') . ' ' . htmlspecialchars($data['familyName'], ENT_QUOTES, 'UTF-8');
    }
}
