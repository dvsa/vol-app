<?php

declare(strict_types=1);

namespace Common\Helper;

/**
 * @see \CommonTest\Helper\StrTest
 */
class Str
{
    /**
     * Determines whether a string contains html.
     *
     * The current implementation of this doesn't have a high degree of accuracy. The original use case would have
     * benefited from more accuracy but it was not required. So if you need more accuracy, you may want to improve this
     * in the future to check the string using regular expressions and an expanded test suite. For now though, this
     * gives a rough idea of whether a string contains html. For example, if a string contains "I <3 PHP" this will
     * be "stripped" to only contain  "I " and thus will show as containing html.
     */
    public static function containsHtml(string $str): bool
    {
        return $str !== strip_tags($str);
    }
}
