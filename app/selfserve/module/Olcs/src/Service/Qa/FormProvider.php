<?php

namespace Olcs\Service\Qa;

use Common\Form\Annotation\CustomAnnotationBuilder;
use Common\Service\Qa\FieldsetPopulator;
use Common\Service\Qa\UsageContext;
use Laminas\Form\Factory as LaminasFormFactory;
use Laminas\Form\InputFilterProviderFieldset;
use RuntimeException;

class FormProvider
{
    /** @var FormFactory */
    private $formFactory;

    /** @var FieldsetPopulator */
    private $fieldsetPopulator;

    /** @var LaminasFormFactory */
    private $laminasFormFactory;

    /** @var CustomAnnotationBuilder */
    private $customAnnotationBuilder;

    /** @var array */
    private $submitOptionsMappings;

    /**
     * Create service instance
     *
     * @param FormFactory $formFactory
     * @param FieldsetPopulator $fieldsetPopulator
     * @param LaminasFormFactory $laminasFormFactory
     * @param CustomAnnotationBuilder $customAnnotationBuilder
     * @param array $submitOptionsMappings
     *
     * @return FormProvider
     */
    public function __construct(
        FormFactory $formFactory,
        FieldsetPopulator $fieldsetPopulator,
        LaminasFormFactory $laminasFormFactory,
        CustomAnnotationBuilder $customAnnotationBuilder,
        array $submitOptionsMappings
    ) {
        $this->formFactory = $formFactory;
        $this->fieldsetPopulator = $fieldsetPopulator;
        $this->laminasFormFactory = $laminasFormFactory;
        $this->customAnnotationBuilder = $customAnnotationBuilder;
        $this->submitOptionsMappings = $submitOptionsMappings;
    }

    /**
     * Get a Form instance corresponding to the supplied form data
     *
     * @param array $options
     * @param string $submitOptionsName
     * @param string $formName
     *
     * @return mixed
     */
    public function get(array $options, $submitOptionsName, $formName)
    {
        if (!isset($this->submitOptionsMappings[$submitOptionsName])) {
            throw new RuntimeException('No submit options mapping found for ' . $submitOptionsName);
        }

        $form = $this->formFactory->create($formName);
        $form->setApplicationStep($options);
        $this->fieldsetPopulator->populate($form, [$options], UsageContext::CONTEXT_SELFSERVE);

        $submitFieldsetSpec = $this->customAnnotationBuilder->getFormSpecification(
            $this->submitOptionsMappings[$submitOptionsName]
        );

        $submitFieldsetSpec['type'] = InputFilterProviderFieldset::class;
        $submitFieldset = $this->laminasFormFactory->create($submitFieldsetSpec);
        $form->add($submitFieldset);

        return $form;
    }
}
