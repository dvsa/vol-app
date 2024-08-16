<?php

/**
 * No Validation Required
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;

/**
 * No Validation Required
 *
 * @IMPORTANT This handler is only used in exceptional circumstances where validation isn't required.
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class NoValidationRequired extends AbstractHandler
{
    /**
     * @inheritdoc
     */
    public function isValid($dto)
    {
        return true;
    }
}
