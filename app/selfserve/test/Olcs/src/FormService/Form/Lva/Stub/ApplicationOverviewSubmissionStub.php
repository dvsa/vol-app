<?php

namespace OlcsTest\FormService\Form\Lva\Stub;

use Olcs\FormService\Form\Lva\ApplicationOverviewSubmission;

/**
 * Stub class for ApplicationOverviewSubmission
 */
class ApplicationOverviewSubmissionStub extends ApplicationOverviewSubmission
{
    #[\Override]
    public function alterForm(\Laminas\Form\FormInterface $form, array $data, array $params)
    {
        parent::alterForm($form, $data, $params);
    }
}
