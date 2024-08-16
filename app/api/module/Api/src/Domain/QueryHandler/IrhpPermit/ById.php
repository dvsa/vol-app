<?php

/**
 * Retrieve an IRHP Permit by id
 *
 * @author Andy Newton <andy@vitri.ltd>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\IrhpPermit;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryByIdHandler;

final class ById extends AbstractQueryByIdHandler
{
    protected $repoServiceName = 'IrhpPermit';
    protected $bundle = [
        'irhpPermitRange' => [
            'countrys' => [
                'country'
            ],
            'irhpPermitStock' => [
                'country'
            ]
            ]
        ];
}
