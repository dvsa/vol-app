<?php

/**
 * Is External User
 */

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;

/**
 * Is External User
 */
class IsExternalUser extends AbstractHandler implements AuthAwareInterface
{
    use AuthAwareTrait;

    /**
     * @inheritdoc
     */
    public function isValid($dto)
    {
        return $this->isExternalUser();
    }
}
