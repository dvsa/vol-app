<?php

/**
 * No Validation Required
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;

/**
 * Side effect validation handler
 *
 * If will always return false (eg fail validation), as side effects aren't validated. But if we accidently exposed
 * a side effect externally it would fail validation
 */
class IsSideEffect extends AbstractHandler
{
    /**
     * @inheritdoc
     */
    public function isValid($dto)
    {
        // always fail validation
        return false;
    }
}
