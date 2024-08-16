<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure;

use Dvsa\Olcs\Api\Service\Qa\Structure\QuestionText\QuestionText;

class SelfservePageFactory
{
    /**
     * Create and return an SelfservePage instance
     *
     * @param string $title
     * @param string $applicationReference
     * @param string $submitOptionsName
     * @param string $nextStepSlug
     *
     * @return SelfservePage
     */
    public function create(
        $title,
        $applicationReference,
        ApplicationStep $applicationStep,
        QuestionText $questionText,
        $submitOptionsName,
        $nextStepSlug
    ) {
        return new SelfservePage(
            $title,
            $applicationReference,
            $applicationStep,
            $questionText,
            $submitOptionsName,
            $nextStepSlug
        );
    }
}
