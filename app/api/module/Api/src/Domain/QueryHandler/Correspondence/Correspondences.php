<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Correspondence;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractListQueryHandler;

/**
 * Correspondences
 */
class Correspondences extends AbstractListQueryHandler
{
    protected $repoServiceName = 'Correspondence';

    protected $bundle = [
        'licence',
        'document',
    ];
}
