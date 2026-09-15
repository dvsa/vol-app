<?php

/**
 * Goods Vehicles
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Data\Mapper\Lva;

use Common\Data\Mapper\MapperInterface;

/**
 * Goods Vehicles
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class GoodsVehicles implements MapperInterface
{
    /**
     * @return (mixed|string)[][]
     *
     * @psalm-return array{data: array{version: mixed, hasEnteredReg: 'N'|'Y'}}
     */
    #[\Override]
    public static function mapFromResult(array $data)
    {
        return [
            'data' => [
                'version' => $data['version'],
                'hasEnteredReg' => $data['hasEnteredReg'] === 'N' ? 'N' : 'Y'
            ]
        ];
    }
}
