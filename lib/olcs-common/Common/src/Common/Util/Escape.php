<?php

namespace Common\Util;

use Laminas\View\Helper\EscapeHtml;
use Laminas\View\Helper\EscapeHtmlAttr;

/**
 * Contains escape functions
 *
 * @author Dmitrij Golubev <dmitrij.golubev@valtech.co.uk>
 */
class Escape
{
    /** @var  callable */
    private static $fncHtml;

    /**
     * Escape a value for HTML content, always returning a string.
     *
     * The cast matters. Laminas's escaper returns anything that is not a string, array or object
     * untouched, so Escape::html(null) is null and Escape::html(123) is an int. That is harmless
     * for security — a non-string cannot carry a payload — but it is a latent fatal: a row value
     * that happens to be absent arrives as null, flows through here unchanged and then hits a
     * `: string` return type. Formatter\ConditionsUndertakingsType did exactly that when a table
     * did not supply conditionType, taking the whole table's render down with a TypeError.
     */
    public static function html($html): string
    {
        if (self::$fncHtml === null) {
            self::$fncHtml = new EscapeHtml();
        }

        $fnc = self::$fncHtml;
        return (string)$fnc($html);
    }

    /**
     * @codeCoverageIgnore only a proxy to Laminas escaper
     */
    public static function htmlAttr($value)
    {
        $escaper = new EscapeHtmlAttr();
        return $escaper($value);
    }
}
