<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Trailer;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;

/**
 * Delete trailer
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class DeleteTrailer extends AbstractHandler implements AuthAwareInterface
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
            if ($this->canAccessTrailer($id) !== true) {
                return false;
            }
        }

        return true;
    }
}
