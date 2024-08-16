<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element;

use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\QaEntityInterface;

interface AnswerClearerInterface
{
    /**
     * Clears from persistent storage the answer data corresponding to the supplied application step and application
     */
    public function clear(QaContext $qaContext);

    /**
     * Whether this answer clearer supports the specified entity
     *
     *
     * @return bool
     */
    public function supports(QaEntityInterface $qaEntity);
}
