<?php

namespace Olcs\Service\Qa;

use Common\Service\Qa\FieldsetPopulator;
use Common\Service\Qa\UsageContext;
use Laminas\Form\Annotation\AnnotationBuilder;
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

    /** @var AnnotationBuilder */
    private $annotationBuilder;

    /** @var array */
    private $submitOptionsMappings;

    /**
     * Create service instance
     *
     * @param FormFactory $formFactory
     * @param FieldsetPopulator $fieldsetPopulator
     * @param LaminasFormFactory $laminasFormFactory
     * @param AnnotationBuilder $annotationBuilder
     * @param array $submitOptionsMappings
     *
     * @return FormProvider
     */
    public function __construct(
        FormFactory $formFactory,
        FieldsetPopulator $fieldsetPopulator,
        LaminasFormFactory $laminasFormFactory,
        $annotationBuilder,
        array $submitOptionsMappings
    ) {
        $this->formFactory = $formFactory;
        $this->fieldsetPopulator = $fieldsetPopulator;
        $this->laminasFormFactory = $laminasFormFactory;
        $this->annotationBuilder = $annotationBuilder;
        $this->submitOptionsMappings = $submitOptionsMappings;
    }

    /**
     * Get a Form instance corresponding to the supplied form data
     *
     * @param array $options
     * @param string $submitOptionsName
     *
     * @return mixed
     */
    public function get(array $options, $submitOptionsName)
    {
        if (!isset($this->submitOptionsMappings[$submitOptionsName])) {
            throw new RuntimeException('No submit options mapping found for ' . $submitOptionsName);
        }

        $form = $this->formFactory->create('QaForm');
        $form->setApplicationStep($options);

        $submitFieldsetSpec = $this->annotationBuilder->getFormSpecification(
            $this->submitOptionsMappings[$submitOptionsName]
        );

        $submitFieldsetSpec['type'] = InputFilterProviderFieldset::class;
        $submitFieldset = $this->laminasFormFactory->create($submitFieldsetSpec);
        $form->add($submitFieldset, ['name' => 'Submit']);

        $this->fieldsetPopulator->populate($form, [$options], UsageContext::CONTEXT_SELFSERVE);

        return $form;
    }
}
