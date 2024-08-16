<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark\Formatter;

/**
 * Organisation name formatter
 */
class OrganisationName implements FormatterInterface
{
    protected static $separator = ' ';

    /**
     * Needs to reset static properties to default
     *
     * @return void
     */
    public static function resetToDefault()
    {
        static::$separator = ' ';
    }

    /**
     * Formats the data
     *
     * @param array $data
     * @return string
     */
    public static function format(array $data)
    {
        $nameParts = [];

        $nameParts[] = $data['name'];

        if (!empty($data['tradingNames'])) {
            $nameParts[] = 'T/A ' . static::getFirstTradingName($data['tradingNames']);
        }

        $result = implode(static::getSeparator(), $nameParts);

        // need to reset static properties to default
        static::resetToDefault();

        return $result;
    }

    /**
     * Gets the first trading name
     *
     * @param array $tradingNames
     * @return string
     */
    private static function getFirstTradingName($tradingNames)
    {
        // we could use usort here, but we don't actually want to sort
        // the whole array; we just want the earliest created so a simple
        // loop is (probably) quicker
        $first = null;
        $name = null;
        foreach ($tradingNames as $tradingName) {
            $current = strtotime((string) $tradingName['createdOn']);
            if ($name === null || $current < $first) {
                $first = $current;
                $name = $tradingName['name'];
            }
        }

        return $name;
    }

    /**
     * @param $separator
     * @return void
     */
    public static function setSeparator($separator)
    {
        static::$separator = $separator;
    }

    /**
     * @return string
     */
    public static function getSeparator()
    {
        return static::$separator;
    }
}
