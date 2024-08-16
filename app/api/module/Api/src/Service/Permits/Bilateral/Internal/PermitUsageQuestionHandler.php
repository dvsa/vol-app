<?php

namespace Dvsa\Olcs\Api\Service\Permits\Bilateral\Internal;

use Dvsa\Olcs\Api\Service\Permits\Bilateral\Common\PermitUsageUpdater;
use Dvsa\Olcs\Api\Service\Qa\QaContext;

class PermitUsageQuestionHandler implements QuestionHandlerInterface
{
    /**
     * Create service instance
     *
     *
     * @return PermitUsageQuestionHandler
     */
    public function __construct(private readonly PermitUsageSelectionGenerator $permitUsageSelectionGenerator, private readonly PermitUsageUpdater $permitUsageUpdater)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QaContext $qaContext, array $requiredPermits)
    {
        $newAnswer = $this->permitUsageSelectionGenerator->generate($requiredPermits);
        $this->permitUsageUpdater->update($qaContext, $newAnswer);
    }
}
