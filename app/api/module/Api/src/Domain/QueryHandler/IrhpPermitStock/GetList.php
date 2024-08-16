<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\IrhpPermitStock;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractListQueryHandler;

/**
 * IRHP Stock
 *
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */
class GetList extends AbstractListQueryHandler
{
    protected $repoServiceName = 'IrhpPermitStock';
    protected $bundle = [
        'irhpPermitType' => ['name'],
        'irhpPermitRanges',
        'irhpPermitWindows',
        'country'
    ];
}
