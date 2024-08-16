<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Cases\Si;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractListQueryHandler;

/**
 * SiList QueryHandler
 */
class SiList extends AbstractListQueryHandler
{
    protected $repoServiceName = 'SeriousInfringement';

    protected $bundle = ['siCategoryType'];
}
