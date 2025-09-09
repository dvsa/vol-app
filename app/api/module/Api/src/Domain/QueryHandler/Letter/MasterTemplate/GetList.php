<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Letter\MasterTemplate;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractListQueryHandler;

/**
 * Get list of MasterTemplates
 */
class GetList extends AbstractListQueryHandler
{
    protected $repoServiceName = 'MasterTemplate';
}
