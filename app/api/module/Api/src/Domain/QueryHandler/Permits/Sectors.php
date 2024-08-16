<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Permits;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractListQueryHandler;

/**
 * Get a list of Sectors
 *
 * @author Tonci Vidovic <tonci.vidovic@capgemini.com>
 */
class Sectors extends AbstractListQueryHandler
{
    protected $repoServiceName = 'Sectors';
}
