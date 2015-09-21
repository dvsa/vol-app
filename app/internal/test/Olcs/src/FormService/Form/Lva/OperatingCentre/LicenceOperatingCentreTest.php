<?php

/**
 * Licence Operating Centre Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace OlcsTest\FormService\Form\Lva\OperatingCentre;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Zend\Form\Fieldset;
use Zend\Form\Form;

/**
 * Licence Operating Centre Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class LicenceOperatingCentreTest extends MockeryTestCase
{
    protected $sut;

    protected $formHelper;

    public function setUp()
    {
        $this->sut = m::mock('Olcs\FormService\Form\Lva\OperatingCentre\LicenceOperatingCentre')
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
