<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\TransportManagerApplication;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Rbac\IdentityProviderInterface;

/**
 * Delete TMA
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class Delete extends AbstractHandler implements AuthAwareInterface
{
    use AuthAwareTrait;

    /**
     * @inheritdoc
     */
    public function isValid($dto)
    {
        $userId = $this->getUser() ? $this->getUser()->getId() : null;
        if ($userId === IdentityProviderInterface::SYSTEM_USER) {
            return true;
        }
        foreach ($dto->getIds() as $id) {
            if ($this->canAccessTransportManagerApplication($id) !== true) {
                return false;
            }
        }

        return true;
    }
}
