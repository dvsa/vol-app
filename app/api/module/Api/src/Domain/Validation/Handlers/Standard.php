<?php

/**
 * Standard
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers;

use Olcs\Logging\Log\Logger;

/**
 * Standard
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Standard extends AbstractHandler
{
    /**
     * @inheritdoc
     */
    public function isValid($dto)
    {
        // Log the fact that we are missing a validation handler
        Logger::info('Missing validation handler...', ['data' => ['dto' => $dto::class]]);

        return true;
    }
}
