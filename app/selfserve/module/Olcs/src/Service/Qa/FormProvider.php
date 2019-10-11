<?php

namespace Olcs\Service\Qa;

use Common\Service\Qa\FieldsetAdder;

class FormProvider
{
    /** @var FormFactory */
    private $formFactory;

    /** @var FieldsetAdder */
    private $fieldsetAdder;

    /**
     * Create service instance
     *
     * @param FormFactory $formFactory
     * @param FieldsetAdder $fieldsetAdder
     *
     * @return FormProvider
     */
    public function __construct(FormFactory $formFactory, FieldsetAdder $fieldsetAdder)
    {
        $this->formFactory = $formFactory;
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
        $form = $this->formFactory->create();
        $form->setApplicationStep($options);
        $this->fieldsetAdder->add($form, $options);
        return $form;
    }
}
