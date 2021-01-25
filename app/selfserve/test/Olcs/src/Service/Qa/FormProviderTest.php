<?php

namespace OlcsTest\Service\Qa;

use Common\Form\Annotation\CustomAnnotationBuilder;
use Common\Service\Qa\FieldsetPopulator;
use Common\Service\Qa\UsageContext;
use Laminas\Form\Factory as LaminasFormFactory;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;
use Laminas\Form\InputFilterProviderFieldset;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Service\Qa\FormFactory;
use Olcs\Service\Qa\FormProvider;
use RuntimeException;

class FormProviderTest extends MockeryTestCase
{
    const FORM_NAME = 'FormName';

    const OPTIONS = [
        'option1Key' => 'option1Value',
        'option2Key' => 'option2Value'
    ];

    const SUBMIT_OPTIONS_NAME = 'submit_options_type_1';
    const SUBMIT_OPTIONS_FQCN = '\Fqcn\For\SubmitOptionsType1';

    const SUBMIT_OPTIONS_MAPPINGS = [
        self::SUBMIT_OPTIONS_NAME => self::SUBMIT_OPTIONS_FQCN
    ];

    private $formFactory;

    private $fieldsetPopulator;

    private $laminasFormFactory;

    private $customAnnotationBuilder;

    private $formProvider;
        
    public function setUp(): void
    {
        $this->formFactory = m::mock(FormFactory::class);

        $this->fieldsetPopulator = m::mock(FieldsetPopulator::class);

        $this->laminasFormFactory = m::mock(LaminasFormFactory::class);

        $this->customAnnotationBuilder = m::mock(CustomAnnotationBuilder::class);

        $this->formProvider = new FormProvider(
            $this->formFactory,
            $this->fieldsetPopulator,
            $this->laminasFormFactory,
            $this->customAnnotationBuilder,
            self::SUBMIT_OPTIONS_MAPPINGS
        );
    }

    public function testGet()
    {
        $annotationBuilderFormSpecification = [
            'attribute1' => 'value1',
            'attribute2' => 'value2',
        ];

        $expectedFormFactoryFormSpecification = [
            'attribute1' => 'value1',
            'attribute2' => 'value2',
            'type' => InputFilterProviderFieldset::class,
        ];

        $fieldset = m::mock(Fieldset::class);

        $form = m::mock(Form::class);
        $form->shouldReceive('setApplicationStep')
            ->with(self::OPTIONS)
            ->once();
        $form->shouldReceive('add')
            ->with($fieldset)
            ->once();

        $this->formFactory->shouldReceive('create')
            ->with(self::FORM_NAME)
            ->once()
            ->andReturn($form);

        $this->fieldsetPopulator->shouldReceive('populate')
            ->with($form, [self::OPTIONS], UsageContext::CONTEXT_SELFSERVE)
            ->once();

        $this->laminasFormFactory->shouldReceive('create')
            ->with($expectedFormFactoryFormSpecification)
            ->once()
            ->andReturn($fieldset);

        $this->customAnnotationBuilder->shouldReceive('getFormSpecification')
            ->with(self::SUBMIT_OPTIONS_FQCN)
            ->once()
            ->andReturn($annotationBuilderFormSpecification);

        $this->assertSame(
            $form,
            $this->formProvider->get(self::OPTIONS, self::SUBMIT_OPTIONS_NAME, self::FORM_NAME)
        );
    }

    public function testGetException()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No submit options mapping found for submit_options_type_xyz');

        $this->formProvider->get(self::OPTIONS, 'submit_options_type_xyz', self::FORM_NAME);
    }
}
