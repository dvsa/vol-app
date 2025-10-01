<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;
use Dvsa\Olcs\Api\Entity\User\Permission;

class CanVerify extends AbstractHandler implements AuthAwareInterface
{
    use AuthAwareTrait;

    /**
     * @param ProcessSignatureResponse $dto
     *
     * @return bool
     */
    public function isValid($dto): bool
    {
        return $this->isOperator();
    }
}
