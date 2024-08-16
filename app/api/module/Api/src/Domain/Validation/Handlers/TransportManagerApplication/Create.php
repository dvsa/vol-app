<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\TransportManagerApplication;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;

/**
 * Create
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class Create extends AbstractHandler
{
    /**
     * @inheritdoc
     */
    public function isValid($dto)
    {
        return $this->canAccessApplication($dto->getApplication()) &&
            $this->canAccessUser($dto->getUser());
    }
}
