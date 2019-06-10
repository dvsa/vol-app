<?php

namespace OlcsTest\Service\Qa;

use Common\Service\Helper\FormHelperService;
use Common\Service\Qa\FieldsetAdder;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Service\Qa\FormProvider;
use Zend\Form\Form;

class FormProviderTest extends MockeryTestCase
{
    public function testGet()
    {
        $options = [
            'option1Key' => 'option1Value',
            'option2Key' => 'option2Value'
        ];

        $form = m::mock(Form::class);

        $formHelperService = m::mock(FormHelperService::class);
        $formHelperService->shouldReceive('createForm')
            ->with('QaForm')
            ->once()
            ->andReturn($form);

        $fieldsetAdder = m::mock(FieldsetAdder::class);
        $fieldsetAdder->shouldReceive('add')
            ->with($form, $options)
            ->once();

        $sut = new FormProvider($formHelperService, $fieldsetAdder);

        $this->assertSame(
            $form,
            $sut->get($options)
        );
    }
}
