<?php

declare(strict_types=1);

namespace OlcsTest\FormService\Form\Lva\Stub;

use Olcs\FormService\Form\Lva\ApplicationOverviewSubmission;

/**
 * Stub class for ApplicationOverviewSubmission
 */
class ApplicationOverviewSubmissionStub extends ApplicationOverviewSubmission
{
    #[\Override]
    public function alterForm(\Laminas\Form\FormInterface $form, array $data, array $params): void
    {
        parent::alterForm($form, $data, $params);
    }
}
