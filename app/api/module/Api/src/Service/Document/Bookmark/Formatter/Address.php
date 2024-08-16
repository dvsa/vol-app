<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark\Formatter;

/**
 * Address formatter
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class Address implements FormatterInterface
{
    protected static $separator = "\n";

    protected static $fields = [
        'addressLine1',
        'addressLine2',
        'addressLine3',
        'addressLine4',
        'town',
        'postcode'
    ];

    /**
     * Needs to reset static properties to default
     *
     * @return void
     */
    public static function resetToDefault()
    {
        static::$separator = "\n";

        static::$fields = [
            'addressLine1',
            'addressLine2',
            'addressLine3',
            'addressLine4',
            'town',
            'postcode'
        ];
    }

    /**
     * Formats the data
     *
     * @param array $data
     * @return string
     */
    public static function format(array $data)
    {
        $address = [];

        foreach (static::getFields() as $key) {
            if (!empty($data[$key])) {
                $address[] = $data[$key];
            }
        }

        if (!empty($data['countryCode']['countryDesc'])) {
            // if provided, include country as well
            $address[] = $data['countryCode']['countryDesc'];
        }

        $result = implode(static::getSeparator(), $address);

        // need to reset static properties to default
        static::resetToDefault();

        return $result;
    }

    /**
     * @return void
     */
    public static function setFields(array $fields)
    {
        static::$fields = $fields;
    }

    /**
     * @return array
     */
    public static function getFields()
    {
        return static::$fields;
    }

    /**
     * @param string $separator
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
