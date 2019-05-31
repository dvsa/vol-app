<?php

namespace Olcs\Service\Qa;

use Common\Service\Helper\FormHelperService as FormHelper;
use Common\Service\Qa\FieldsetAdder;

class FormProvider
{
    /** @var FormHelper */
    private $formHelper;

    /** @var FieldsetAdder */
    private $fieldsetAdder;

    /**
     * Create service instance
     *
     * @param FormHelper $formHelper
     * @param FieldsetAdder $fieldsetAdder
     *
     * @return FormProvider
     */
    public function __construct(FormHelper $formHelper, FieldsetAdder $fieldsetAdder)
    {
        $this->formHelper = $formHelper;
        $this->fieldsetAdder = $fieldsetAdder;
    }

    /**
     * Get a Form instance corresponding to the supplied form data
     *
     * @param array $options
     *
     * @return mixed
     */
    public function get(array $options)
    {
        $form = $this->formHelper->createForm('QaForm');
        $this->fieldsetAdder->add($form, $options);
        return $form;
    }
}
