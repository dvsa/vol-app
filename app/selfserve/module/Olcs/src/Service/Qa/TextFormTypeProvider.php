<?php

namespace Olcs\Service\Qa;

use Common\Service\Helper\FormHelperService as FormHelper;

class TextFormTypeProvider implements FormTypeProviderInterface
{
    /** @var FormHelper */
    private $formHelper;

    /** @var ValidatorsAdder */
    private $validatorsAdder;

    /**
     * Create service instance
     *
     * @param FormHelper $formHelper
     * @param ValidatorsAdder $validatorsAdder
     *
     * @return TextFormTypeProvider
     */
    public function __construct(FormHelper $formHelper, ValidatorsAdder $validatorsAdder)
    {
        $this->formHelper = $formHelper;
        $this->validatorsAdder = $validatorsAdder;
    }

    /**
     * {@inheritdoc}
     */
    public function get(array $data, array $validators)
    {
        $form = $this->formHelper->createForm('QaTextForm');

        $fieldsets = $form->getFieldsets();
        $fieldsFieldset = $fieldsets['fields'];

        $elements = $fieldsFieldset->getElements();
        $textElement = $elements['qaElement'];

        $textElement->setValue($data['value']);
        $textElement->setLabel($data['label']);
        $textElement->setOption('hint', $data['hint']);

        $this->validatorsAdder->add($form, $validators);

        return $form;
    }
}
