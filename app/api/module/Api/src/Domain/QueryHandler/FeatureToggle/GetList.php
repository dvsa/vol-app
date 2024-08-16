<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\FeatureToggle;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractListQueryHandler;

/**
 * FeatureToggle
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class GetList extends AbstractListQueryHandler
{
    protected $repoServiceName = 'FeatureToggle';
}
