<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;

/**
 * Can Access Transaction with a transaction reference
 */
class CanAccessTransactionWithRef extends AbstractHandler
{
    /**
     * @inheritdoc
     */
    public function isValid($dto)
    {
        return $this->canAccessTransaction($dto->getReference());
    }
}
