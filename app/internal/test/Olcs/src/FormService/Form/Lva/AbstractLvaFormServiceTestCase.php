<?php

/**
 * Abstract LVA Form Service Test Case
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\FormService\Form\Lva;

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
    /** @var  \Common\FormService\Form\AbstractFormService */
    protected $sut;

    public function setUp()
    {
        $this->formHelper = m::mock(\Common\Service\Helper\FormHelperService::class);
        $this->fsm = m::mock(\Common\FormService\FormServiceManager::class)->makePartial();

        $class = $this->classToTest;
        $this->sut = new $class();
        $this->sut->setFormHelper($this->formHelper);
        $this->sut->setFormServiceLocator($this->fsm);
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
                m::mock()
                    ->shouldReceive('get')
                    ->with('save')
                    ->once()
                    ->andReturn(
                        m::mock()
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
