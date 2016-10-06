<?php

namespace OlcsTest\FormService\Form\Lva\Stub;

use Olcs\FormService\Form\Lva\AbstractOverviewSubmission;

/**
 * Stub Class form AbstractOverviewSubmission
 */
class AbstractOverviewSubmissionStub extends AbstractOverviewSubmission
{
    public function alterForm(\Zend\Form\FormInterface $form, array $data, array $params)
    {
        parent::alterForm($form, $data, $params);
    }

    public function hasSectionsWithStatus($status)
    {
        return parent::hasSectionsWithStatus($status);
    }
}
