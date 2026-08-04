<?php

namespace Common\View\Helper;

use Laminas\View\Helper\AbstractHelper;

/**
 * Format Currency in the system appropriately
 *
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */
class CurrencyFormatter extends AbstractHelper
{
    public const PREFIX = '£';

    /**
     * Return a formatted Monetary Value
     *
     * @param string|null $value Parameters
     */
    public function __invoke(?string $value): string
    {
        $components = explode('.', $value);
        if (count($components) > 2) {
            return self::PREFIX . $value;
        }

        $pounds = strrev(wordwrap(strrev($components[0]), 3, ',', true));
        $pence = '00';

        if (count($components) == 2) {
            $pence = $components[1];
        }

        $formatted = self::PREFIX . $pounds;
        if ($pence !== '00') {
            $formatted .= '.' . $pence;
        }

        return htmlspecialchars($formatted);
    }
}
