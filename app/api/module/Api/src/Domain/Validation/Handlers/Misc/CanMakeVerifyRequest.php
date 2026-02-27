<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;

class CanMakeVerifyRequest extends AbstractHandler implements AuthAwareInterface
{
    use AuthAwareTrait;


    /**
     * @inheritdoc
     */
    #[\Override]
    public function isValid($dto)
    {
        return ($this->isOperator() || $this->isTransportManager());
    }
}
