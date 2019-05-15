<?php

namespace Olcs\Service\Qa;

use Common\Service\Helper\FormHelperService as FormHelper;

class SingleCheckboxFormTypeProvider implements FormTypeProviderInterface
{
    /** @var FormHelper */
    private $formHelper;

    /**
     * Create service instance
     *
     * @param FormHelper $formHelper
     *
     * @return SingleCheckboxFormTypeProvider
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
        $form = $this->formHelper->createForm('QaSingleCheckboxForm');

        $fieldsets = $form->getFieldsets();
        $fieldsFieldset = $fieldsets['fields'];

        $elements = $fieldsFieldset->getElements();
        $checkboxElement = $elements['qaElement'];

        $checkboxElement->setLabel($data['label']);
        $checkboxElement->setChecked($data['checked']);
        $checkboxElement->setOption('not_checked_message', $data['not_checked_message']);

        return $form;
    }
}
