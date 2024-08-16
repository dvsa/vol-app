<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\FeeType;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractListQueryHandler;

/**
 * Get a list of all Fee Types. optionally filtered
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
class GetList extends AbstractListQueryHandler
{
    protected $repoServiceName = 'FeeType';
}
