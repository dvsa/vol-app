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
    /**
     * Create service instance
     *
     * @param AnnotationBuilder $annotationBuilder
     *
     * @return FormProvider
     */
    public function __construct(private FormFactory $formFactory, private FieldsetPopulator $fieldsetPopulator, private LaminasFormFactory $laminasFormFactory, private $annotationBuilder, private array $submitOptionsMappings)
    {
    }

    /**
     * Get a Form instance corresponding to the supplied form data
     *
     * @param string $submitOptionsName
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
