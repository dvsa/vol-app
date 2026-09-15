<?php

namespace Common\FormService\Form\Lva;

use Common\Service\Helper\FormHelperService;
use Laminas\Form\Form;
use Laminas\Http\Request;

/**
 * FinancialEvidenceAssessment Form
 */
class FinancialEvidenceAssessment extends AbstractLvaFormService
{
    public function __construct(protected FormHelperService $formHelper)
    {
    }

    public function getForm(Request $request): Form
    {
        $form = $this->formHelper->createFormWithRequest('Lva\FinancialEvidenceAssessment', $request);

        $this->alterForm($form);

        return $form;
    }

    protected function alterForm($form): void
    {
        // Hook for any form alterations (e.g. removing fields, adding hints)
    }
}

