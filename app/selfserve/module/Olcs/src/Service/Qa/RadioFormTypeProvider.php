<?php

namespace Olcs\Service\Qa;

use Common\Service\Helper\FormHelperService as FormHelper;

class RadioFormTypeProvider
{
    /** @var FormHelper */
    private $formHelper;

    /**
     * Create service instance
     *
     * @param FormHelper $formHelper
     *
     * @return RadioFormTypeProvider
     */
    public function __construct(FormHelper $formHelper)
    {
        $this->formHelper = $formHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function get(array $data, array $validators)
    {
        $form = $this->formHelper->createForm('QaRadioForm');

        $fieldsets = $form->getFieldsets();
        $fieldsFieldset = $fieldsets['fields'];

        $elements = $fieldsFieldset->getElements();
        $radioElement = $elements['qaElement'];
        $radioElement->setValueOptions($data['options']);

        return $form;
    }
}
