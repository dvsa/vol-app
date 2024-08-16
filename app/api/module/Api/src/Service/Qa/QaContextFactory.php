<?php

namespace Dvsa\Olcs\Api\Service\Qa;

use Dvsa\Olcs\Api\Entity\Generic\ApplicationStep as ApplicationStepEntity;

class QaContextFactory
{
    /**
     * Create and return an QaContext instance
     *
     *
     * @return QaContext
     */
    public function create(ApplicationStepEntity $applicationStepEntity, QaEntityInterface $qaEntity)
    {
        return new QaContext($applicationStepEntity, $qaEntity);
    }
}
