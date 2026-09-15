<?php

/**
 * Company Subsidiary
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Data\Mapper\Lva;

use Common\Data\Mapper\MapperInterface;

/**
 * Company Subsidiary
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CompanySubsidiary implements MapperInterface
{
    /**
     * @return array[]
     *
     * @psalm-return array{data: array}
     */
    #[\Override]
    public static function mapFromResult(array $data)
    {
        return [
            'data' => $data
        ];
    }
}
