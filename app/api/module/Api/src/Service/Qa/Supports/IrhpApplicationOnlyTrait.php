<?php

namespace Dvsa\Olcs\Api\Service\Qa\Supports;

use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Service\Qa\QaEntityInterface;

trait IrhpApplicationOnlyTrait
{
    /**
     * Whether the passed entity is of type IrhpApplication
     *
     *
     * @return bool
     */
    public function supports(QaEntityInterface $qaEntity)
    {
        return $qaEntity instanceof IrhpApplication;
    }
}
