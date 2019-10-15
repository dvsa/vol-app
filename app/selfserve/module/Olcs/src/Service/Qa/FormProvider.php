<?php

namespace Olcs\Service\Qa;

use Common\Service\Qa\FieldsetPopulator;

class FormProvider
{
    /** @var FormFactory */
    private $formFactory;

    /** @var FieldsetPopulator */
    private $fieldsetPopulator;

    /**
     * Create service instance
     *
     * @param FormFactory $formFactory
     * @param FieldsetPopulator $fieldsetPopulator
     *
     * @return FormProvider
     */
    public function __construct(FormFactory $formFactory, FieldsetPopulator $fieldsetPopulator)
    {
        $this->formFactory = $formFactory;
        $this->fieldsetPopulator = $fieldsetPopulator;
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
        $this->fieldsetPopulator->populate($form, [$options]);
        return $form;
    }
}
