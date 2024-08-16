<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\PreviousConviction;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;

/**
 * CreatePreviousConviction
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class DeletePreviousConviction extends AbstractHandler implements AuthAwareInterface
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
            if ($this->canAccessPreviousConviction($id) !== true) {
                return false;
            }
        }

        return true;
    }
}
