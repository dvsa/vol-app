<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\OtherLicence;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;

/**
 * Modify OtherLicence
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class Modify extends AbstractHandler implements AuthAwareInterface
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

        foreach ($dto->getIds() as $id) {
            if ($this->canAccessOtherLicence($id) !== true) {
                return false;
            }
        }

        return true;
    }
}
