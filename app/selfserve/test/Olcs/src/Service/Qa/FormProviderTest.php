<?php

namespace OlcsTest\Service\Qa;

use Common\Service\Qa\FieldsetPopulator;
use Common\Service\Qa\UsageContext;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Service\Qa\FormProvider;
use Olcs\Service\Qa\FormFactory;
use Laminas\Form\Form;

class FormProviderTest extends MockeryTestCase
{
    public function testGet()
    {
        $formName = 'FormName';

        $options = [
            'option1Key' => 'option1Value',
            'option2Key' => 'option2Value'
        ];

        $form = m::mock(Form::class);
        $form->shouldReceive('setApplicationStep')
            ->with($options)
            ->once();

        $formFactory = m::mock(FormFactory::class);
        $formFactory->shouldReceive('create')
            ->with($formName)
            ->once()
            ->andReturn($form);

        $fieldsetPopulator = m::mock(FieldsetPopulator::class);
        $fieldsetPopulator->shouldReceive('populate')
            ->with($form, [$options], UsageContext::CONTEXT_SELFSERVE)
            ->once();

        $sut = new FormProvider($formFactory, $fieldsetPopulator);

        $this->assertSame(
            $form,
            $sut->get($options, $formName)
        );
    }
}
