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

    public static function html($html)
    {
        if (self::$fncHtml === null) {
            self::$fncHtml = new EscapeHtml();
        }

        $fnc = self::$fncHtml;
        return $fnc($html);
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
