<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\TransportManagerApplication;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;

/**
 * GetList
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class GetList extends AbstractHandler implements AuthAwareInterface
{
    use AuthAwareTrait;

    /**
     * @inheritdoc
     */
    public function isValid($dto)
    {
        if (!empty($dto->getApplication())) {
            return $this->canAccessApplication($dto->getApplication());
        }

        if (!empty($dto->getUser())) {
            return $this->getCurrentUser()->getId() == $dto->getUser();
        }

        return $this->isInternalUser();
    }
}
