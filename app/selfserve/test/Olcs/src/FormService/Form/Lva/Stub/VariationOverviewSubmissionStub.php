<?php

namespace OlcsTest\FormService\Form\Lva\Stub;

use Olcs\FormService\Form\Lva\VariationOverviewSubmission;

/**
 * Stub class for VariationOverviewSubmission
 */
class VariationOverviewSubmissionStub extends VariationOverviewSubmission
{
    public function alterForm(\Zend\Form\FormInterface $form, array $data, array $params)
    {
        parent::alterForm($form, $data, $params);
    }
}
