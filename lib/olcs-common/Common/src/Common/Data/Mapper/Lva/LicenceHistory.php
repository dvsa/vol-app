<?php

/**
 * Licence History
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Common\Data\Mapper\Lva;

/**
 * Licence History
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class LicenceHistory
{
    /**
     * @return array[]
     *
     * @psalm-return array{data: array}
     */
    public static function mapFromResult(array $data): array
    {
        return [
            'data' => $data
        ];
    }
}
