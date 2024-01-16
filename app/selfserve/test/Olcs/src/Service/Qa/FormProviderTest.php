<?php

namespace OlcsTest\Service\Qa;

use ArrayObject;
use Laminas\Form\Annotation\AnnotationBuilder;
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
    public const OPTIONS = [
        'option1Key' => 'option1Value',
        'option2Key' => 'option2Value'
    ];

    public const SUBMIT_OPTIONS_NAME = 'submit_options_type_1';
    public const SUBMIT_OPTIONS_FQCN = '\Fqcn\For\SubmitOptionsType1';

    public const SUBMIT_OPTIONS_MAPPINGS = [
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

        $this->annotationBuilder = m::mock(new AnnotationBuilder())->makePartial();

        $this->formProvider = new FormProvider(
            $this->formFactory,
            $this->fieldsetPopulator,
            $this->laminasFormFactory,
            $this->annotationBuilder,
            self::SUBMIT_OPTIONS_MAPPINGS
        );
    }

    public function testGet()
    {
        $annotationBuilderFormSpecification = new ArrayObject(
            [
                'attribute1' => 'value1',
                'attribute2' => 'value2',
            ]
        );

        $fieldset = m::mock(Fieldset::class);

        $form = m::mock(Form::class);
        $form->shouldReceive('setApplicationStep')
            ->with(self::OPTIONS)
            ->once();
        $form->shouldReceive('add')
            ->with($fieldset, ['name' => 'Submit'])
            ->once()
            ->globally()
            ->ordered();

        $this->formFactory->shouldReceive('create')
            ->with('QaForm')
            ->once()
            ->andReturn($form);

        $this->fieldsetPopulator->shouldReceive('populate')
            ->with($form, [self::OPTIONS], UsageContext::CONTEXT_SELFSERVE)
            ->once()
            ->globally()
            ->ordered();

        $this->laminasFormFactory->shouldReceive('create')
            ->with(m::on(function ($arg) {
                return $arg instanceof ArrayObject &&
                    isset($arg['attribute1']) &&
                    isset($arg['attribute2']) &&
                    $arg['attribute1'] === 'value1' &&
                    $arg['attribute2'] === 'value2';
            }))
            ->once()
            ->andReturn($fieldset);

        $this->annotationBuilder->shouldReceive('getFormSpecification')
            ->with(self::SUBMIT_OPTIONS_FQCN)
            ->once()
            ->andReturn($annotationBuilderFormSpecification);

        $this->assertSame(
            $form,
            $this->formProvider->get(self::OPTIONS, self::SUBMIT_OPTIONS_NAME)
        );
    }

    public function testGetException()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No submit options mapping found for submit_options_type_xyz');

        $this->formProvider->get(self::OPTIONS, 'submit_options_type_xyz', 'QaForm');
    }
}
