<?php

/**
 * Document subcategory formatter
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */

namespace Common\Service\Table\Formatter;

use Common\Util\Escape;

/**
 * Document subcategory formatter
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class DocumentSubcategory implements FormatterPluginManagerInterface
{
    /**
     * Format a address
     *
     * @param  array $data
     * @param  array $column
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {
        $str = Escape::html($data['documentSubCategoryName']);
        if ($data['isExternal']) {
            $str .= ' (selfserve)';
        }

        if ($data['ciId']) {
            $str .= ' (emailed)';
        }

        return $str;
    }
}
