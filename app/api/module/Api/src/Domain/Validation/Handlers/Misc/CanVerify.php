<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Transfer\Command\GdsVerify\ProcessSignatureResponse;
use Dvsa\Olcs\Transfer\Query\GdsVerify\GetAuthRequest;

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
        return $this->isOperator() || $this->isValidAccess($dto);
    }


    /**
     * isValidAccess
     * @param $dto
     *
     * @return bool
     */
    private function isValidAccess($dto): bool
    {
        if (
            $this->isTransportManager()
            && $dto instanceof ProcessSignatureResponse
            && method_exists($dto, 'getTransportManagerApplication')
        ) {
            return $dto->getTransportManagerApplication() > 0;
        }
        return false;
    }
}
