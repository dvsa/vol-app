<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\FeatureToggle;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractDeleteCommandHandler;

/**
 * Delete a feature toggle
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
final class Delete extends AbstractDeleteCommandHandler
{
    protected $repoServiceName = 'FeatureToggle';
}
