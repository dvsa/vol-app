<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark\Formatter;

/**
 * AbstractArrayList formatter
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class AbstractArrayList implements FormatterInterface
{
    public const FORMAT = '(%s)';
    public const SEPARATOR = ', ';
    public const COLUMN = '';

    /**
     * format a list which comes from an array, where the same array key is needed from each iteration
     *
     * produces a string of configurable format, e.g. '(entry1, entry2, entry 3)'
     * for examples of usage see:
     *
     * Dvsa\Olcs\Api\Service\Document\Bookmark\Formatter\BrOtherServiceNos
     * Dvsa\Olcs\Api\Service\Document\Bookmark\Formatter\BrTasNotified
     *
     * @param array $data data
     *
     * @return string
     */
    public static function format(array $data)
    {
        if (!empty($data)) {
            return sprintf(
                static::FORMAT,
                implode(
                    static::SEPARATOR,
                    array_column($data, static::COLUMN)
                )
            );
        }

        return '';
    }
}
