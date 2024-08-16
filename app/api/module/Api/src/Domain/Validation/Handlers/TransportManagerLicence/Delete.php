<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\TransportManagerLicence;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Rbac\IdentityProviderInterface;
use Dvsa\Olcs\Transfer\Command\TransportManagerLicence\Delete as DeleteDto;

/**
 * Delete Transport manager licence
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class Delete extends AbstractHandler implements AuthAwareInterface
{
    use AuthAwareTrait;

    /**
     * Whether a user has permission to delete transport manager licence records
     *
     * @param DeleteDto $dto delete command
     *
     * @return bool
     */
    public function isValid($dto)
    {
        $userId = $this->getUser() ? $this->getUser()->getId() : null;

        if ($userId === IdentityProviderInterface::SYSTEM_USER) {
            return true;
        }

        foreach ($dto->getIds() as $id) {
            if ($this->canAccessTransportManagerLicence($id) !== true) {
                return false;
            }
        }

        return true;
    }
}
