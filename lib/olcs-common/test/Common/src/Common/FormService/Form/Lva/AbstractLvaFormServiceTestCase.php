<?php

namespace CommonTest\Common\FormService\Form\Lva;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Form\Form;

/**
 * Abstract LVA Form Service Test Case
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
abstract class AbstractLvaFormServiceTestCase extends MockeryTestCase
{
    protected $classToTest = 'override_me';

    protected $formName = 'override_me_too';

    protected $sut;

    protected $formHelper;

    protected $fsm;

    protected $classArgs = [];

    #[\Override]
    protected function setUp(): void
    {
        $this->formHelper = m::mock(\Common\Service\Helper\FormHelperService::class);
        $this->fsm = m::mock(\Common\FormService\FormServiceManager::class)->makePartial();
        $this->classArgs = array_merge([$this->formHelper], $this->classArgs);
        $reflector = new \ReflectionClass($this->classToTest);
        $this->sut = $reflector->newInstanceArgs($this->classArgs);
    }

    public function testGetForm(): void
    {
        // Mocks
        $mockForm = m::mock(Form::class)->shouldReceive('get')->withAnyArgs()->getMock();

        $this->formHelper->shouldReceive('createForm')
            ->with($this->formName)
            ->andReturn($mockForm);

        $form = $this->sut->getForm();

        $this->assertSame($mockForm, $form);
    }
}
