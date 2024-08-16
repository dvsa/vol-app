<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;

/**
 * Can Access Fee With Id
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CanAccessFeeWithId extends AbstractHandler implements AuthAwareInterface
{
    use AuthAwareTrait;

    /**
     * @inheritdoc
     */
    public function isValid($dto)
    {
        if ($this->isInternalUser()) {
            return true;
        }
        return $this->canAccessFee($this->getId($dto));
    }

    protected function getId($dto)
    {
        return $dto->getId();
    }
}
