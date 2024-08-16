<?php

namespace Dvsa\Olcs\Api\Service\Qa\Supports;

use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Service\Qa\QaEntityInterface;

trait IrhpPermitApplicationOnlyTrait
{
    /**
     * Whether the passed entity is of type IrhpPermitApplication
     *
     *
     * @return bool
     */
    public function supports(QaEntityInterface $qaEntity)
    {
        return $qaEntity instanceof IrhpPermitApplication;
    }
}
