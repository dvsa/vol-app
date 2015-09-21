<?php

/**
 * Application Operating Centre Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace OlcsTest\FormService\Form\Lva\OperatingCentre;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Zend\Form\Fieldset;
use Zend\Form\Form;

/**
 * Application Operating Centre Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ApplicationOperatingCentreTest extends MockeryTestCase
{
    protected $sut;

    protected $formHelper;

    public function setUp()
    {
        $this->sut = m::mock('Olcs\FormService\Form\Lva\OperatingCentre\ApplicationOperatingCentre')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods(true);
    }

    public function testAlterForm()
    {
        $form = m::mock(Form::class);
        $this->sut->shouldReceive('alterForm')
            ->with($form, [])
            ->once()
            ->getMock();
        $this->sut->alterForm($form, []);
    }
}
