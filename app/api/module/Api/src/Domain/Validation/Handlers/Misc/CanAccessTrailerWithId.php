<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;

/**
 * Can Access Trailer With ID
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CanAccessTrailerWithId extends AbstractHandler
{
    /**
     * @inheritdoc
     */
    public function isValid($dto)
    {
        return $this->canAccessTrailer($this->getId($dto));
    }

    protected function getId($dto)
    {
        return $dto->getId();
    }
}
