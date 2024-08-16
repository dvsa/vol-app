<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;

/**
 * Can Access Transport Manager Licence With ID
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class CanAccessTmlWithId extends AbstractHandler implements AuthAwareInterface
{
    use AuthAwareTrait;

    /**
     * @inheritdoc
     */
    public function isValid($dto)
    {
        return $this->canAccessTransportManagerLicence($this->getId($dto));
    }

    protected function getId($dto)
    {
        return $dto->getId();
    }
}
