<?php

/**
 * Business Type
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Data\Mapper\Lva;

use Common\Data\Mapper\MapperInterface;

/**
 * Business Type
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class BusinessType implements MapperInterface
{
    /**
     * @return (array|mixed)[]
     *
     * @psalm-return array{version: mixed, data: array{type: mixed}}
     */
    #[\Override]
    public static function mapFromResult(array $data)
    {
        return [
            'version' => $data['version'],
            'data' => [
                'type' => $data['type']['id']
            ]
        ];
    }
}
