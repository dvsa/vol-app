<?php

namespace Common\View\Helper;

use Laminas\View\Helper\AbstractHelper;

/**
 * Return To Address
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ReturnToAddress extends AbstractHelper
{
    protected static $addresses = [
        'ni' => [
            'Department for Infrastructure',
            'Quarry House',
            'Leeds',
            'LS2 7UE',
        ],
        'gb' => [
            'Office of the Traffic Commissioner',
            'Quarry House',
            'Leeds',
            'LS2 7UE',
        ]
    ];

    /**
     * Print out address
     *
     * @param bool   $isNi      Is NI
     * @param string $separator Default line separator
     *
     * @return string
     */
    public function __invoke($isNi = false, $separator = ', ')
    {
        return self::getAddress($isNi, $separator);
    }

    /**
     * Print out address
     *
     * @param bool   $isNi      Is NI
     * @param string $separator Default line separator
     *
     * @return string
     */
    public static function getAddress($isNi = false, $separator = ', ')
    {
        if ($isNi) {
            return implode($separator, self::$addresses['ni']);
        }

        return implode($separator, self::$addresses['gb']);
    }
}
