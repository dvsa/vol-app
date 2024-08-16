<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\FeatureToggle;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryByIdHandler;

/**
 * Retrieve a feature toggle by id
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
final class ById extends AbstractQueryByIdHandler
{
    protected $repoServiceName = 'FeatureToggle';
}
