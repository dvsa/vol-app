<?php

namespace OlcsTest\FormService\Form\Lva;

use Laminas\Form\ElementInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Abstract LVA Form Service Test Case
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
abstract class AbstractLvaFormServiceTestCase extends MockeryTestCase
{
    protected $classToTest = 'override_me';

    /** @var  m\MockInterface */
    protected $formHelper;
    /** @var  m\MockInterface */
    protected $fsm;

    protected $sut;
    protected $classArgs = [];

    public function setUp(): void
    {
        $this->formHelper = m::mock(\Common\Service\Helper\FormHelperService::class);
        $this->fsm = m::mock(\Common\FormService\FormServiceManager::class)->makePartial();
        $this->classArgs = array_merge([$this->formHelper], $this->classArgs);
        $reflector = new \ReflectionClass($this->classToTest);
        $this->sut = $reflector->newInstanceArgs($this->classArgs);
    }

    public function testGetForm()
    {
        // Mocks
        $mockForm = m::mock(\Common\Form\Form::class);

        $this->formHelper->shouldReceive('createForm')
            ->andReturn($mockForm);

        $mockForm
            ->shouldReceive('get')
            ->with('form-actions')
            ->once()
            ->andReturn(
                m::mock(ElementInterface::class)
                    ->shouldReceive('get')
                    ->with('save')
                    ->once()
                    ->andReturn(
                        m::mock(ElementInterface::class)
                            ->shouldReceive('setLabel')
                            ->with('internal.save.button')
                            ->once()
                            ->getMock()
                    )
                    ->getMock()
            )
            ->getMock();

        $form = $this->sut->getForm();

        $this->assertSame($mockForm, $form);
    }
}
