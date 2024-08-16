<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;

/**
 * Can Access Transport Manager Application With ID
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class CanAccessTmaWithId extends AbstractHandler implements AuthAwareInterface
{
    use AuthAwareTrait;

    /**
     * @inheritdoc
     */
    public function isValid($dto)
    {
        return $this->canAccessTransportManagerApplication($this->getId($dto));
    }

    protected function getId($dto)
    {
        if (method_exists($dto, 'getTmaId')) {
            return $dto->getTmaId();
        }

        return $dto->getId();
    }
}
