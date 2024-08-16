<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\IrhpPermitType;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractListQueryHandler;

/**
 * IRHP Type
 *
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */
class GetList extends AbstractListQueryHandler
{
    protected $repoServiceName = 'IrhpPermitType';
}
