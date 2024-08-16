<?php

namespace Dvsa\Olcs\Api\Service\Permits\Bilateral\Common;

use Dvsa\Olcs\Api\Service\Qa\QaContext;

class StandardAndCabotageUpdater
{
    /**
     * Create service instance
     *
     *
     * @return StandardAndCabotageUpdater
     */
    public function __construct(private readonly ModifiedAnswerUpdater $modifiedAnswerUpdater)
    {
    }

    /**
     * Update the standard and cabotage value relating to a specific country within a bilateral application
     *
     * @param string $newAnswer
     */
    public function update(QaContext $qaContext, $newAnswer)
    {
        $this->modifiedAnswerUpdater->update(
            $qaContext,
            $qaContext->getQaEntity()->getBilateralCabotageSelection(),
            $newAnswer
        );
    }
}
