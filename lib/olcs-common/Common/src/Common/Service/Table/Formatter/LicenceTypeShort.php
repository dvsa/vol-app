<?php

/**
 * Licence Type Short formatter
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Service\Table\Formatter;

use Common\RefData;

/**
 * Licence Type Short formatter
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceTypeShort implements FormatterPluginManagerInterface
{
    protected static array $prefixMap = [
        RefData::LICENCE_CATEGORY_GOODS_VEHICLE => 'GV',
        RefData::LICENCE_CATEGORY_PSV => 'PSV'
    ];

    protected static array $suffixMap = [
        RefData::LICENCE_TYPE_RESTRICTED => 'R',
        RefData::LICENCE_TYPE_SPECIAL_RESTRICTED => 'SR',
        RefData::LICENCE_TYPE_STANDARD_NATIONAL => 'SN',
        RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL => 'SI'
    ];

    /**
     * Retrieve a nested value
     *
     * @param  array $data
     * @return string
     */
    #[\Override]
    public function format($data, $column = null)
    {
        $ref = [];

        $licence = $data['licence'] ?? $data;

        if (
            isset($licence['goodsOrPsv']['id'])
            && isset(self::$prefixMap[$licence['goodsOrPsv']['id']])
        ) {
            $ref[] = self::$prefixMap[$licence['goodsOrPsv']['id']];
        }

        if (
            isset($licence['licenceType']['id'])
            && isset(self::$suffixMap[$licence['licenceType']['id']])
        ) {
            $ref[] = self::$suffixMap[$licence['licenceType']['id']];
        }

        return implode('-', $ref);
    }
}
