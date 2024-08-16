<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;

/**
 * Is user Intern admin permission
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class IsInternalAdmin extends AbstractHandler implements AuthAwareInterface
{
    use AuthAwareTrait;

    /**
     * @inheritdoc
     */
    public function isValid($dto)
    {
        return ($this->isGranted(\Dvsa\Olcs\Api\Entity\User\Permission::INTERNAL_ADMIN));
    }
}
