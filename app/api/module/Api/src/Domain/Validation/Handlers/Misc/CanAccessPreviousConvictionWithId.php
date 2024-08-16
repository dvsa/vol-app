<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;

/**
 * Can Access PreviousConviction With Id
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class CanAccessPreviousConvictionWithId extends AbstractHandler
{
    /**
     * @inheritdoc
     */
    public function isValid($dto)
    {
        return $this->canAccessPreviousConviction($this->getId($dto));
    }

    protected function getId($dto)
    {
        return $dto->getId();
    }
}
