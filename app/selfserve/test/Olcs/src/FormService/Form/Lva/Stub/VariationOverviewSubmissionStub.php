<?php

declare(strict_types=1);

namespace OlcsTest\FormService\Form\Lva\Stub;

use Olcs\FormService\Form\Lva\VariationOverviewSubmission;

/**
 * Stub class for VariationOverviewSubmission
 */
class VariationOverviewSubmissionStub extends VariationOverviewSubmission
{
    #[\Override]
    public function alterForm(\Laminas\Form\FormInterface $form, array $data, array $params): void
    {
        parent::alterForm($form, $data, $params);
    }
}
