<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Template;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractListQueryHandler;

/**
 * Available templates
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class AvailableTemplates extends AbstractListQueryHandler
{
    protected $repoServiceName = 'Template';
}
