<?php

namespace Dvsa\Olcs\Api\Service\Permits\Bilateral\Internal;

use Dvsa\Olcs\Api\Service\Qa\QaContext;

interface QuestionHandlerInterface
{
    /**
     * Handle the persistence of the appropriate database content for this QA context and the specified required
     * permits data
     */
    public function handle(QaContext $qaContext, array $requiredPermits);
}
